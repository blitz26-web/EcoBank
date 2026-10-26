<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LedgerType;
use App\Exceptions\InsufficientPointsException;
use App\Models\PointLedger;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class PointLedgerService
{
    public function credit(User $holder, int $amount, Model $reference, string $description): PointLedger
    {
        $this->assertPositive($amount);

        $balanceAfter = $holder->balance_points + $amount;

        $holder->balance_points = $balanceAfter;
        $holder->save();

        return $this->record($holder, LedgerType::Credit, $amount, $reference, $description, $balanceAfter);
    }

    public function debit(User $holder, int $amount, Model $reference, string $description): PointLedger
    {
        $this->assertPositive($amount);

        if ($amount > $holder->balance_points) {
            throw new InsufficientPointsException(sprintf(
                'Saldo poin tidak cukup: saldo saat ini %d poin, dibutuhkan %d poin.',
                $holder->balance_points,
                $amount,
            ));
        }

        $balanceAfter = $holder->balance_points - $amount;

        $holder->balance_points = $balanceAfter;
        $holder->save();

        return $this->record($holder, LedgerType::Debit, $amount, $reference, $description, $balanceAfter);
    }

    private function assertPositive(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Jumlah poin mutasi harus lebih besar dari nol.');
        }
    }

    private function record(
        User $holder,
        LedgerType $type,
        int $amount,
        Model $reference,
        string $description,
        int $balanceAfter,
    ): PointLedger {
        return PointLedger::query()->create([
            'user_id' => $holder->getKey(),
            'type' => $type,
            'amount' => $amount,
            'reference_type' => $reference->getMorphClass(),
            'reference_id' => $reference->getKey(),
            'description' => $description,
            'balance_after' => $balanceAfter,
        ]);
    }
}
