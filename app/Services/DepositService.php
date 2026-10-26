<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WasteCategory;
use App\Notifications\DepositVerifiedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class DepositService
{
    public function __construct(private readonly PointLedgerService $ledger)
    {
    }

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

        $deposit = DB::transaction(function () use ($holder, $items, $officer, $initialStatus): Deposit {
            $holder = $this->lockUser((int) $holder->getKey());

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

        if ($deposit->status === DepositStatus::Completed) {
            $this->notifyHolder($deposit);
        }

        return $deposit;
    }

    public function completeDeposit(Deposit $deposit, User $officer): Deposit
    {
        $deposit = DB::transaction(function () use ($deposit, $officer): Deposit {
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

        $this->notifyHolder($deposit);

        return $deposit;
    }

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

    private function notifyHolder(Deposit $deposit): void
    {
        try {
            $deposit->user->notify(new DepositVerifiedNotification($deposit));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function lockUser(int $userId): User
    {
        return User::query()
            ->whereKey($userId)
            ->lockForUpdate()
            ->firstOrFail();
    }

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
