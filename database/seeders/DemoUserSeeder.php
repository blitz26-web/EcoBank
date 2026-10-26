<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\PointLedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function __construct(private readonly PointLedgerService $ledger)
    {
    }

    private const DEMO_ACCOUNTS = [
        ['admin@kashijau.test', 'Admin Kas Hijau', '081200000001', UserRole::Admin, 0, null, null],
        ['petugas@kashijau.test', 'Petugas Loket 1', '081200000002', UserRole::Petugas, 0, null, null],
        ['warga@kashijau.test', 'Budi Santoso', '081200000003', UserRole::Warga, 1500, 'Tabungan Kompos Rumah', 5000],
        ['sari@kashijau.test', 'Sari Wulandari', '081200000004', UserRole::Warga, 0, null, null],
    ];

    public function run(): void
    {
        $password = Hash::make('password');

        foreach (self::DEMO_ACCOUNTS as [$email, $name, $phone, $role, $initialPoints, $goalTitle, $goalPoints]) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'password' => $password,
                    'role' => $role,
                    'balance_points' => 0,
                    'email_verified_at' => now(),
                    'savings_goal_title' => $goalTitle,
                    'savings_goal_points' => $goalPoints,
                ],
            );

            $label = sprintf('%-22s => %s / password', $email, $role->label());
            $this->command->line("Akun demo: $label");

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
                        'Saldo awal (seeder).',
                    );
                });
            }
        }
    }
}
