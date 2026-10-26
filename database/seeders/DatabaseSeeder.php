<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Mengisi data awal Kas Hijau...');

        $this->call([
            WasteCategorySeeder::class,
            DemoUserSeeder::class,
            DummyUserSeeder::class,
            DemoTransactionSeeder::class,
        ]);

        $this->command->info('Selesai. Akun demo & dummy tercantum di output seeder.');
    }
}
