<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder utama EcoBank.
 *
 * Menjalankan seeder demo secara berurutan:
 *  1. WasteCategorySeeder    - kategori sampah contoh (idempotent).
 *  2. DemoUserSeeder         - akun admin, petugas, dan warga (idempotent).
 *  3. DummyUserSeeder        - 12 akun warga dummy saldo bervariasi
 *                              (idempotent, saldo via ledger).
 *  4. DemoTransactionSeeder  - setoran & penarikan demo lewat Service agar
 *                              ledger tetap rekonsiliasi (idempotent).
 *
 * Jalankan:
 *   php artisan migrate --seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Mengisi data awal EcoBank...');

        $this->call([
            WasteCategorySeeder::class,
            DemoUserSeeder::class,
            DummyUserSeeder::class,
            DemoTransactionSeeder::class,
        ]);

        $this->command->info('Selesai. Akun demo & dummy tercantum di output seeder.');
    }
}
