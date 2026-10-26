<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WasteCategory;
use App\Services\DepositService;
use App\Services\WithdrawalService;
use Illuminate\Database\Seeder;

class DemoTransactionSeeder extends Seeder
{
    public function run(): void
    {
        if (Deposit::query()->exists()) {
            $this->command->line('  - DemoTransactionSeeder dilewati (sudah ada data setoran).');

            return;
        }

        $warga = User::query()->where('email', 'warga@kashijau.test')->first();
        $sari = User::query()->where('email', 'sari@kashijau.test')->first();
        $petugas = User::query()->where('email', 'petugas@kashijau.test')->first();

        if ($warga === null || $petugas === null) {
            $this->command->warn('  - DemoTransactionSeeder dilewati: akun demo tidak ditemukan (jalankan DemoUserSeeder).');

            return;
        }

        $categories = WasteCategory::query()->get()->keyBy('name');

        if ($categories->isEmpty()) {
            $this->command->warn('  - DemoTransactionSeeder dilewati: kategori sampah kosong (jalankan WasteCategorySeeder).');

            return;
        }

        $deposits = app(DepositService::class);
        $withdrawals = app(WithdrawalService::class);

        $deposits->createDeposit($warga, [
            ['waste_category_id' => $categories['Botol PET']->getKey(), 'weight_in_kg' => 3.5],
            ['waste_category_id' => $categories['Kardus / Duplex']->getKey(), 'weight_in_kg' => 2.0],
        ], $petugas, DepositStatus::Completed);

        $deposits->createDeposit($warga, [
            ['waste_category_id' => $categories['Kaleng Aluminium']->getKey(), 'weight_in_kg' => 1.2],
            ['waste_category_id' => $categories['Plastik Kresek']->getKey(), 'weight_in_kg' => 0.5],
        ], null, DepositStatus::Pending);

        if ($sari !== null) {
            $deposits->createDeposit($sari, [
                ['waste_category_id' => $categories['Kertas HVS']->getKey(), 'weight_in_kg' => 2.0],
            ], null, DepositStatus::Pending);

            $deposits->createDeposit($sari, [
                ['waste_category_id' => $categories['Kaleng Aluminium']->getKey(), 'weight_in_kg' => 2.0],
            ], $petugas, DepositStatus::Completed);
        }

        $processed = $withdrawals->requestWithdrawal(
            $warga,
            300,
            \App\Models\Withdrawal::PAYMENT_CASH,
        );
        $withdrawals->processWithdrawal($processed, $petugas);

        $rejected = $withdrawals->requestWithdrawal(
            $warga,
            200,
            \App\Models\Withdrawal::PAYMENT_E_WALLET,
            '081234567890',
        );
        $withdrawals->rejectWithdrawal($rejected, $petugas, 'Nomor e-wallet tidak terdaftar atas nama warga.');

        $withdrawals->requestWithdrawal(
            $warga,
            250,
            \App\Models\Withdrawal::PAYMENT_BANK_TRANSFER,
            '1234567890',
        );

        $this->command->info('  - Transaksi demo dibuat: 4 setoran (2 selesai, 2 pending), 3 penarikan (1 diproses, 1 ditolak, 1 diajukan).');
    }
}
