<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LedgerType;
use App\Exceptions\InsufficientPointsException;
use App\Models\PointLedger;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Service bersama pemilik logika mutasi saldo poin (kredit/debit).
 *
 * Ini SATU-SATUNYA tempat di mana users.balance_points boleh diubah,
 * sehingga aturan imutabilitas ledger mustahil terlewat: setiap mutasi
 * selalu diikuti ENTRI BARU di point_ledgers yang mencatat saldo
 * setelah mutasi (balance_after) sebagai jejak audit.
 *
 * Dipakai bersama oleh DepositService (kredit poin setoran) dan
 * WithdrawalService (debit pengajuan tarik / kredit refund penolakan).
 *
 * Kedua metode mengasumsikan PEMANGGIL sudah:
 *  1. Membungkus operasi dalam DB::transaction(), dan
 *  2. Mengunci baris user dengan lockForUpdate().
 */
class PointLedgerService
{
    /**
     * Kreditkan (tambah) poin ke saldo user.
     *
     * @param  User $holder  User yang barisnya SUDAH dikunci lockForUpdate().
     * @param  int $amount   Jumlah poin (selalu positif).
     * @param  Model $reference  Model pemicu (Deposit, Withdrawal, dll).
     * @param  string $description  Keterangan manusiawi untuk audit.
     *
     * @throws InvalidArgumentException Bila jumlah tidak positif.
     */
    public function credit(User $holder, int $amount, Model $reference, string $description): PointLedger
    {
        $this->assertPositive($amount);

        $balanceAfter = $holder->balance_points + $amount;

        $holder->balance_points = $balanceAfter;
        $holder->save();

        return $this->record($holder, LedgerType::Credit, $amount, $reference, $description, $balanceAfter);
    }

    /**
     * Debitkan (kurangi) poin dari saldo user.
     *
     * @param  User $holder  User yang barisnya SUDAH dikunci lockForUpdate().
     * @param  int $amount   Jumlah poin (selalu positif).
     * @param  Model $reference  Model pemicu (Withdrawal, dll).
     * @param  string $description  Keterangan manusiawi untuk audit.
     *
     * @throws InsufficientPointsException Bila jumlah melebihi saldo saat ini.
     * @throws InvalidArgumentException Bila jumlah tidak positif.
     */
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

    /* ------------------------------------------------------------------ */
    /* Private helpers                                                     */
    /* ------------------------------------------------------------------ */

    private function assertPositive(int $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Jumlah poin mutasi harus lebih besar dari nol.');
        }
    }

    /**
     * Catat entri ledger baru (audit trail imutabel) dan kembalikan entri itu.
     */
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
