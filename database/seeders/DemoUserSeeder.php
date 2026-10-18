<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\PointLedgerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat akun demo untuk tiap peran (idempotent - aman dijalankan berulang).
 *
 * Keputusan desain:
 * - Saldo awal warga TIDAK di-set langsung ke kolom balance_points, melainkan
 *   dikreditkan lewat PointLedgerService sehingga tercipta entri point_ledgers.
 *   Dengan demikian saldo tetap cocok dengan ledger saat rekonsiliasi (Langkah 5).
 * - Referensi entri ledger saldo awal adalah model User itu sendiri, dengan
 *   deskripsi eksplisit "Saldo awal (seeder)" agar mudah dikenali saat audit.
 */
class DemoUserSeeder extends Seeder
{
    public function __construct(private readonly PointLedgerService $ledger)
    {
    }

    /**
     * [email, nama, telepon, peran, saldo awal poin]
     *
     * @var list<array{0: string, 1: string, 2: string, 3: UserRole, 4: int}>
     */
    private const DEMO_ACCOUNTS = [
        ['admin@kashijau.test', 'Admin Kas Hijau', '081200000001', UserRole::Admin, 0],
        ['petugas@kashijau.test', 'Petugas Loket 1', '081200000002', UserRole::Petugas, 0],
        ['warga@kashijau.test', 'Budi Santoso', '081200000003', UserRole::Warga, 1500],
        ['sari@kashijau.test', 'Sari Wulandari', '081200000004', UserRole::Warga, 0],
    ];

    public function run(): void
    {
        $password = Hash::make('password');

        foreach (self::DEMO_ACCOUNTS as [$email, $name, $phone, $role, $initialPoints]) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'password' => $password,
                    'role' => $role,
                    'balance_points' => 0,
                    // Akun demo langsung terverifikasi agar bisa langsung
                    // melewati middleware `verified` saat pertama kali login.
                    'email_verified_at' => now(),
                ],
            );

            $label = sprintf('%-22s => %s / password', $email, $role->label());
            $this->command->line("Akun demo: $label");

            // Kredit saldo awal hanya untuk akun BARU, agar seeder idempotent
            // (tidak menumpuk entri ledger bila dijalankan ulang).
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
