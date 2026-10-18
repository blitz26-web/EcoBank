<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WithdrawalStatus;
use App\Exceptions\InsufficientPointsException;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service yang menangani seluruh alur bisnis penarikan poin Kas Hijau.
 *
 * Pola HOLD: poin dipotong dari saldo saat pengajuan dibuat (requested)
 * agar saldo tidak bisa dipakai ganda oleh beberapa pengajuan yang
 * berjalan bersamaan. Bila penarikan ditolak, poin dikembalikan
 * otomatis lewat entri kredit di ledger.
 *
 * Semua operasi atomik (DB::transaction) dan setiap mutasi saldo
 * dicatat ke point_ledgers melalui PointLedgerService.
 */
class WithdrawalService
{
    public function __construct(private readonly PointLedgerService $ledger)
    {
    }

    /**
     * Ajukan penarikan poin menjadi uang tunai.
     *
     * @param  User $holder  Warga pemilik saldo poin.
     * @param  int $pointsAmount  Jumlah poin yang ditarik (harus <= saldo).
     * @param  string $paymentMethod  Salah satu konstanta PAYMENT_* model Withdrawal.
     * @param  string|null $accountNumber  No. rekening/e-wallet; wajib untuk non-tunai.
     *
     * @throws InsufficientPointsException Bila saldo poin tidak mencukupi.
     * @throws InvalidArgumentException Bila jumlah di luar batas konfigurasi,
     *                                  metode tidak valid, atau nomor rekening kosong.
     */
    public function requestWithdrawal(
        User $holder,
        int $pointsAmount,
        string $paymentMethod,
        ?string $accountNumber = null,
    ): Withdrawal {
        if (! in_array($paymentMethod, $this->allowedPaymentMethods(), true)) {
            throw new InvalidArgumentException('Metode pembayaran tidak valid.');
        }

        // Normalisasi: penarikan tunai tidak menyimpan nomor rekening.
        if ($paymentMethod === Withdrawal::PAYMENT_CASH) {
            $accountNumber = null;
        } elseif ($accountNumber === null || trim($accountNumber) === '') {
            throw new InvalidArgumentException('Nomor rekening/e-wallet wajib diisi untuk pembayaran non-tunai.');
        }

        $this->assertAmountWithinLimits($pointsAmount);

        return DB::transaction(function () use ($holder, $pointsAmount, $paymentMethod, $accountNumber): Withdrawal {
            // Kunci baris user: cek saldo dan debit harus atomik terhadap
            // setoran / penarikan lain yang berjalan bersamaan.
            $holder = User::query()
                ->whereKey((int) $holder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $withdrawal = Withdrawal::query()->create([
                'withdrawal_code' => $this->generateWithdrawalCode(),
                'user_id' => $holder->getKey(),
                'points_amount' => $pointsAmount,
                'cash_amount' => $this->convertPointsToRupiah($pointsAmount),
                'payment_method' => $paymentMethod,
                'account_number' => $accountNumber,
                'status' => WithdrawalStatus::Requested,
            ]);

            // Debit saldo + catat ledger. Melempar InsufficientPointsException
            // bila saldo tidak cukup; seluruh transaksi otomatis di-rollback.
            $this->ledger->debit(
                $holder,
                $pointsAmount,
                $withdrawal,
                sprintf('Penarikan poin %s.', $withdrawal->withdrawal_code),
            );

            return $withdrawal->fresh();
        });
    }

    /**
     * Tandai penarikan sudah dibayar (tunai/transfer tersalurkan).
     * Tidak mengubah saldo: poin sudah dipotong saat pengajuan.
     *
     * @throws RuntimeException Bila penarikan tidak berstatus requested.
     */
    public function processWithdrawal(Withdrawal $withdrawal, User $officer): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal, $officer): Withdrawal {
            $withdrawal = $this->lockWithdrawal((int) $withdrawal->getKey());

            if (! $withdrawal->isRequested()) {
                throw new RuntimeException(sprintf(
                    'Penarikan %s tidak dapat diproses karena statusnya "%s".',
                    $withdrawal->withdrawal_code,
                    $withdrawal->status->label(),
                ));
            }

            $withdrawal->update([
                'status' => WithdrawalStatus::Processed,
                'officer_id' => $officer->getKey(),
                'processed_at' => now(),
            ]);

            return $withdrawal->fresh();
        });
    }

    /**
     * Tolak penarikan: tandai rejected DAN kembalikan poin yang di-hold
     * ke saldo warga beserta entri kredit di ledger (audit trail utuh).
     *
     * @throws InvalidArgumentException Bila alasan penolakan kosong.
     * @throws RuntimeException Bila penarikan tidak berstatus requested.
     */
    public function rejectWithdrawal(Withdrawal $withdrawal, User $officer, string $reason): Withdrawal
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('Alasan penolakan wajib diisi untuk kebutuhan audit.');
        }

        return DB::transaction(function () use ($withdrawal, $officer, $reason): Withdrawal {
            $withdrawal = $this->lockWithdrawal((int) $withdrawal->getKey());

            if (! $withdrawal->isRequested()) {
                throw new RuntimeException(sprintf(
                    'Penarikan %s tidak dapat ditolak karena statusnya "%s".',
                    $withdrawal->withdrawal_code,
                    $withdrawal->status->label(),
                ));
            }

            // Kunci baris warga sebelum pengembalian poin.
            $holder = User::query()
                ->whereKey((int) $withdrawal->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $withdrawal->update([
                'status' => WithdrawalStatus::Rejected,
                'officer_id' => $officer->getKey(),
                'rejected_reason' => trim($reason),
            ]);

            // Kembalikan poin yang tertahan (hold) sejak pengajuan.
            $this->ledger->credit(
                $holder,
                $withdrawal->points_amount,
                $withdrawal,
                sprintf('Pengembalian poin: penarikan %s ditolak. Alasan: %s', $withdrawal->withdrawal_code, trim($reason)),
            );

            return $withdrawal->fresh();
        });
    }

    /**
     * Daftar metode pembayaran yang valid — dipakai ulang oleh
     * StoreWithdrawalRequest untuk rule `in:` pada validasi form.
     *
     * @return list<string>
     */
    public function allowedPaymentMethods(): array
    {
        return [
            Withdrawal::PAYMENT_BANK_TRANSFER,
            Withdrawal::PAYMENT_E_WALLET,
            Withdrawal::PAYMENT_CASH,
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Private helpers                                                     */
    /* ------------------------------------------------------------------ */

    /**
     * Validasi jumlah poin terhadap batas konfigurasi aplikasi.
     */
    private function assertAmountWithinLimits(int $pointsAmount): void
    {
        if ($pointsAmount <= 0) {
            throw new InvalidArgumentException('Jumlah poin penarikan harus lebih besar dari nol.');
        }

        $min = (int) config('kas-hijau.min_withdrawal_points', 0);
        $max = (int) config('kas-hijau.max_withdrawal_points', 0);

        if ($min > 0 && $pointsAmount < $min) {
            throw new InvalidArgumentException(sprintf(
                'Minimal penarikan adalah %s poin.',
                number_format((float) $min, 0, ',', '.'),
            ));
        }

        if ($max > 0 && $pointsAmount > $max) {
            throw new InvalidArgumentException(sprintf(
                'Maksimal penarikan adalah %s poin.',
                number_format((float) $max, 0, ',', '.'),
            ));
        }
    }

    /**
     * Konversi poin ke Rupiah memakai nilai tukar dari konfigurasi server.
     * Rate TIDAK pernah diterima dari klien agar tidak bisa dimanipulasi.
     */
    private function convertPointsToRupiah(int $pointsAmount): float
    {
        $pointValue = (int) config('kas-hijau.point_value', 0);

        if ($pointValue <= 0) {
            throw new RuntimeException('Konfigurasi kas-hijau.point_value belum diatur dengan benar (harus lebih besar dari 0).');
        }

        return round($pointsAmount * $pointValue, 2);
    }

    /**
     * Ambil ulang penarikan dengan row lock (SELECT ... FOR UPDATE).
     * WAJIB dipanggil dari dalam DB::transaction().
     */
    private function lockWithdrawal(int $withdrawalId): Withdrawal
    {
        return Withdrawal::query()
            ->whereKey($withdrawalId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Bangun kode penarikan unik, contoh: WDR-20261010-AB12CD.
     * Unique index pada kolom withdrawal_code menjadi pengaman terakhir
     * terhadap tabrakan kode akibat request yang bersamaan.
     */
    private function generateWithdrawalCode(): string
    {
        do {
            $code = sprintf(
                'WDR-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (Withdrawal::query()->where('withdrawal_code', $code)->exists());

        return $code;
    }
}
