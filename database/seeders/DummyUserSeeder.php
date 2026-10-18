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
 * Membuat akun warga DUMMY untuk memperkaya data demo (permintaan user).
 *
 * Berbeda dengan DemoUserSeeder yang menampung akun "resmi" tiap peran,
 * seeder ini menambahkan belasan warga dengan saldo bervariasi supaya
 * statistik poin beredar di dashboard admin dan daftar warga di form loket
 * terlihat hidup saat demo/pengujian.
 *
 * Aturan yang SAMA dengan DemoUserSeeder:
 *  - Idempotent: firstOrCreate + kredit saldo hanya saat akun BARU dibuat,
 *    sehingga menjalankan seeder berulang tidak menumpuk entri ledger.
 *  - Saldo TIDAK pernah di-set langsung ke kolom balance_points; selalu
 *    dikreditkan lewat PointLedgerService::credit() di dalam
 *    DB::transaction + lockForUpdate agar invarian
 *    "saldo = rekap point_ledgers" tetap terjaga persis seperti alur
 *    produksi.
 *  - email_verified_at diisi now() sebagai bekal bila verifikasi email
 *    diaktifkan kembali di kemudian hari.
 */
class DummyUserSeeder extends Seeder
{
    /**
     * [email, nama, telepon, saldo awal poin]. Semua berperan Warga dan
     * memakai password yang sama dengan akun demo: "password".
     *
     * @var list<array{0: string, 1: string, 2: string, 3: int}>
     */
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

            // Kredit saldo awal hanya untuk akun BARU (idempotent) dan selalu
            // lewat ledger, bukan set kolom balance_points langsung.
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
