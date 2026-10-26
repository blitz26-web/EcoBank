<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WithdrawalStatus;
use App\Exceptions\InsufficientPointsException;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\WithdrawalProcessedNotification;
use App\Notifications\WithdrawalRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class WithdrawalService
{
    public function __construct(private readonly PointLedgerService $ledger)
    {
    }

    public function requestWithdrawal(
        User $holder,
        int $pointsAmount,
        string $paymentMethod,
        ?string $accountNumber = null,
    ): Withdrawal {
        if (! in_array($paymentMethod, $this->allowedPaymentMethods(), true)) {
            throw new InvalidArgumentException('Metode pembayaran tidak valid.');
        }

        if ($paymentMethod === Withdrawal::PAYMENT_CASH) {
            $accountNumber = null;
        } elseif ($accountNumber === null || trim($accountNumber) === '') {
            throw new InvalidArgumentException('Nomor rekening/e-wallet wajib diisi untuk pembayaran non-tunai.');
        }

        $this->assertAmountWithinLimits($pointsAmount);

        return DB::transaction(function () use ($holder, $pointsAmount, $paymentMethod, $accountNumber): Withdrawal {
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

            $this->ledger->debit(
                $holder,
                $pointsAmount,
                $withdrawal,
                sprintf('Penarikan poin %s.', $withdrawal->withdrawal_code),
            );

            return $withdrawal->fresh();
        });
    }

    public function processWithdrawal(Withdrawal $withdrawal, User $officer): Withdrawal
    {
        $withdrawal = DB::transaction(function () use ($withdrawal, $officer): Withdrawal {
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

            return $withdrawal->fresh(['user']);
        });

        try {
            $withdrawal->user->notify(new WithdrawalProcessedNotification($withdrawal));
        } catch (Throwable $e) {
            report($e);
        }

        return $withdrawal;
    }

    public function rejectWithdrawal(Withdrawal $withdrawal, User $officer, string $reason): Withdrawal
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('Alasan penolakan wajib diisi untuk kebutuhan audit.');
        }

        $withdrawal = DB::transaction(function () use ($withdrawal, $officer, $reason): Withdrawal {
            $withdrawal = $this->lockWithdrawal((int) $withdrawal->getKey());

            if (! $withdrawal->isRequested()) {
                throw new RuntimeException(sprintf(
                    'Penarikan %s tidak dapat ditolak karena statusnya "%s".',
                    $withdrawal->withdrawal_code,
                    $withdrawal->status->label(),
                ));
            }

            $holder = User::query()
                ->whereKey((int) $withdrawal->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $withdrawal->update([
                'status' => WithdrawalStatus::Rejected,
                'officer_id' => $officer->getKey(),
                'rejected_reason' => trim($reason),
            ]);

            $this->ledger->credit(
                $holder,
                $withdrawal->points_amount,
                $withdrawal,
                sprintf('Pengembalian poin: penarikan %s ditolak. Alasan: %s', $withdrawal->withdrawal_code, trim($reason)),
            );

            return $withdrawal->fresh(['user']);
        });

        try {
            $withdrawal->user->notify(new WithdrawalRejectedNotification($withdrawal));
        } catch (Throwable $e) {
            report($e);
        }

        return $withdrawal;
    }

    public function allowedPaymentMethods(): array
    {
        return [
            Withdrawal::PAYMENT_BANK_TRANSFER,
            Withdrawal::PAYMENT_E_WALLET,
            Withdrawal::PAYMENT_CASH,
        ];
    }

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

    private function convertPointsToRupiah(int $pointsAmount): float
    {
        $pointValue = (int) config('kas-hijau.point_value', 0);

        if ($pointValue <= 0) {
            throw new RuntimeException('Konfigurasi kas-hijau.point_value belum diatur dengan benar (harus lebih besar dari 0).');
        }

        return round($pointsAmount * $pointValue, 2);
    }

    private function lockWithdrawal(int $withdrawalId): Withdrawal
    {
        return Withdrawal::query()
            ->whereKey($withdrawalId)
            ->lockForUpdate()
            ->firstOrFail();
    }

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
