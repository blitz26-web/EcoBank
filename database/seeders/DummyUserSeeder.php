<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\PointLedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    private const DUMMY_ACCOUNTS = [
        ['andi@kashijau.test', 'Andi Pratama', '081200000005', 2450],
        ['dewi@kashijau.test', 'Dewi Lestari', '081200000006', 1830],
        ['agus@kashijau.test', 'Agus Wijaya', '081200000007', 920],
        ['rina@kashijau.test', 'Rina Marlina', '081200000008', 3750],
        ['joko@kashijau.test', 'Joko Prasetyo', '081200000009', 640],
        ['fitri@kashijau.test', 'Fitri Handayani', '081200000010', 2880],
        ['rudi@kashijau.test', 'Rudi Hartono', '081200000011', 1500],
        ['nina@kashijau.test', 'Nina Kurnia', '081200000012', 110],
        ['eko@kashijau.test', 'Eko Prabowo', '081200000013', 5000],
        ['lina@kashijau.test', 'Lina Kartika', '081200000014', 2210],
        ['doni@kashijau.test', 'Doni Firmansyah', '081200000015', 760],
        ['maya@kashijau.test', 'Maya Anggraini', '081200000016', 3400],
    ];

    public function __construct(private readonly PointLedgerService $ledger)
    {
    }

    public function run(): void
    {
        $password = Hash::make('password');

        foreach (self::DUMMY_ACCOUNTS as [$email, $name, $phone, $initialPoints]) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'password' => $password,
                    'role' => UserRole::Warga,
                    'balance_points' => 0,
                    'email_verified_at' => now(),
                ],
            );

            $label = sprintf('%-20s => %s / saldo %d poin / password', $email, $name, $initialPoints);
            $this->command->line("Akun dummy: $label");

            if ($initialPoints > 0 && $user->wasRecentlyCreated) {
                DB::transaction(function () use ($user, $initialPoints): void {
                    $locked = User::query()
                        ->whereKey((int) $user->getKey())
                        ->lockForUpdate()
                        ->firstOrFail();

                    $this->ledger->credit(
                        $locked,
                        $initialPoints,
                        $locked,
                        'Saldo awal (dummy seeder).',
                    );
                });
            }
        }
    }
}
