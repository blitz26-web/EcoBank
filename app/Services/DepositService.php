<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WasteCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service yang menangani seluruh alur bisnis penyetoran sampah EcoBank.
 *
 * Prinsip utama:
 *  1. ATOMIK     — semua operasi dibungkus DB::transaction(); saldo warga,
 *                  rincian item, dan entri point ledger hanya tersimpan
 *                  bila SELURUH proses berhasil.
 *  2. TERKUNCI   — baris user dan deposit dikunci dengan lockForUpdate()
 *                  (SELECT ... FOR UPDATE) untuk mencegah race condition
 *                  ketika saldo diubah oleh transaksi bersamaan.
 *  3. AUDITABLE  — setiap perubahan users.balance_points WAJIB mencatat
 *                  entri baru ke point_ledgers (ledger bersifat imutabel),
 *                  didelegasikan ke PointLedgerService sebagai satu pintu.
 *  4. AMAN       — poin dihitung ulang di sisi server; angka dari klien
 *                  tidak pernah dipercaya.
 */
class DepositService
{
    public function __construct(private readonly PointLedgerService $ledger)
    {
    }

    /**
     * Buat penyetoran baru beserta rinciannya.
     *
     * Dua skenario yang didukung:
     *  1. Warga mengajukan setoran mandiri   -> status Pending, poin BELUM masuk.
     *  2. Petugas mencatat setoran di loket  -> status Completed, poin langsung masuk.
     *
     * @param  User $holder        Warga pemilik rekening poin.
     * @param  array<int, array{waste_category_id: int|string, weight_in_kg: float|int|string}> $items
     * @param  User|null $officer  Petugas yang mencatat (opsional).
     * @param  DepositStatus $initialStatus  Status awal setoran (Pending atau Completed).
     *
     * @throws InvalidArgumentException Bila daftar item kosong, berat tidak valid,
     *                                  atau status awal tidak sah.
     */
    public function createDeposit(
        User $holder,
        array $items,
        ?User $officer = null,
        DepositStatus $initialStatus = DepositStatus::Pending,
    ): Deposit {
        if ($items === []) {
            throw new InvalidArgumentException('Minimal satu jenis sampah harus disetorkan.');
        }

        if ($initialStatus === DepositStatus::Cancelled) {
            throw new InvalidArgumentException('Setoran baru tidak dapat dibuat dengan status dibatalkan.');
        }

        return DB::transaction(function () use ($holder, $items, $officer, $initialStatus): Deposit {
            // Kunci baris user agar saldo tidak berubah oleh transaksi lain
            // selama proses ini berjalan (mencegah race condition).
            $holder = $this->lockUser((int) $holder->getKey());

            // Validasi + hitung poin di sisi server sebelum menulis ke database.
            $preparedItems = $this->prepareItems($items);
            $totalPoints = array_sum(array_column($preparedItems, 'points'));

            $deposit = Deposit::query()->create([
                'deposit_code' => $this->generateDepositCode(),
                'user_id' => $holder->getKey(),
                'officer_id' => $officer?->getKey(),
                'total_points' => $totalPoints,
                'status' => $initialStatus,
            ]);

            foreach ($preparedItems as $prepared) {
                $deposit->items()->create([
                    'waste_category_id' => $prepared['category']->getKey(),
                    'weight_in_kg' => $prepared['weight'],
                    'points_earned' => $prepared['points'],
                ]);
            }

            // Poin hanya dikreditkan apabila setoran langsung selesai diverifikasi.
            if ($initialStatus === DepositStatus::Completed) {
                $this->ledger->credit(
                    $holder,
                    $totalPoints,
                    $deposit,
                    sprintf('Poin dari setoran %s.', $deposit->deposit_code),
                );
            }

            return $deposit->fresh(['items.wasteCategory', 'user', 'officer']);
        });
    }

    /**
     * Verifikasi setoran berstatus Pending: tandai selesai dan kreditkan
     * poin ke saldo warga beserta catatan ledger.
     *
     * @throws RuntimeException Bila setoran sudah diproses sebelumnya (bukan Pending).
     */
    public function completeDeposit(Deposit $deposit, User $officer): Deposit
    {
        return DB::transaction(function () use ($deposit, $officer): Deposit {
            // Kunci baris setoran agar tidak bisa diverifikasi / dibatalkan ganda.
            $deposit = Deposit::query()
                ->whereKey($deposit->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $deposit->isPending()) {
                throw new RuntimeException(sprintf(
                    'Setoran %s tidak dapat diverifikasi karena statusnya sudah "%s".',
                    $deposit->deposit_code,
                    $deposit->status->label(),
                ));
            }

            $holder = $this->lockUser((int) $deposit->user_id);

            $deposit->update([
                'status' => DepositStatus::Completed,
                'officer_id' => $officer->getKey(),
            ]);

            $this->ledger->credit(
                $holder,
                $deposit->total_points,
                $deposit,
                sprintf('Poin dari verifikasi setoran %s.', $deposit->deposit_code),
            );

            return $deposit->fresh(['items.wasteCategory', 'user', 'officer']);
        });
    }

    /**
     * Batalkan setoran yang masih Pending.
     *
     * Setoran berstatus Completed TIDAK dibatalkan di sini — pengembalian
     * poin harus dilakukan lewat entri ledger debit terpisah agar audit
     * trail tetap konsisten (implementasi menyusul pada langkah berikutnya).
     *
     * @throws RuntimeException Bila setoran tidak berstatus Pending.
     */
    public function cancelDeposit(Deposit $deposit): Deposit
    {
        return DB::transaction(function () use ($deposit): Deposit {
            $deposit = Deposit::query()
                ->whereKey($deposit->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $deposit->isPending()) {
                throw new RuntimeException(sprintf(
                    'Setoran %s tidak dapat dibatalkan karena statusnya sudah "%s".',
                    $deposit->deposit_code,
                    $deposit->status->label(),
                ));
            }

            $deposit->update(['status' => DepositStatus::Cancelled]);

            return $deposit;
        });
    }

    /* ------------------------------------------------------------------ */
    /* Private helpers                                                     */
    /* ------------------------------------------------------------------ */

    /**
     * Ambil ulang user dengan row lock (SELECT ... FOR UPDATE).
     * WAJIB dipanggil dari dalam DB::transaction().
     */
    private function lockUser(int $userId): User
    {
        return User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Validasi & hitung poin setiap item di sisi server.
     *
     * @param  array<int, array{waste_category_id: int|string, weight_in_kg: float|int|string}> $items
     * @return array<int, array{category: WasteCategory, weight: float, points: int}>
     */
    private function prepareItems(array $items): array
    {
        $prepared = [];

        foreach ($items as $index => $item) {
            $categoryId = (int) ($item['waste_category_id'] ?? 0);
            $weight = (float) ($item['weight_in_kg'] ?? 0);

            if ($categoryId <= 0) {
                throw new InvalidArgumentException(sprintf(
                    'Item #%d: waste_category_id wajib diisi.',
                    $index + 1,
                ));
            }

            if ($weight <= 0.0) {
                throw new InvalidArgumentException(sprintf(
                    'Item #%d: berat sampah harus lebih besar dari nol.',
                    $index + 1,
                ));
            }

            $category = WasteCategory::query()->findOrFail($categoryId);

            $prepared[] = [
                'category' => $category,
                'weight' => $weight,
                'points' => $category->calculatePoints($weight),
            ];
        }

        return $prepared;
    }

    /**
     * Bangun kode setoran unik, contoh: DEP-20261010-7F3K9Q.
     *
     * Unique index pada kolom deposit_code berperan sebagai pengaman
     * terakhir terhadap tabrakan kode akibat request yang bersamaan.
     */
    private function generateDepositCode(): string
    {
        do {
            $code = sprintf(
                'DEP-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (Deposit::query()->where('deposit_code', $code)->exists());

        return $code;
    }
}
