<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Tampilan pagination memakai markup Bootstrap 5 (pagination links,
         * tombol prev/next) — default Laravel bergaya Tailwind sehingga
         * wajib diarahkan ke preset Bootstrap untuk stack Kas Hijau.
         */
        Paginator::useBootstrapFive();

        /*
         * Admin = super user: semua ability policy otomatis lolos tanpa
         * perlu mencabang isAdmin() di setiap method policy. Mengembalikan
         * null berarti evaluasi berlanjut ke method policy masing-masing
         * (berlaku untuk warga dan petugas).
         */
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->isAdmin() ? true : null;
        });

        /*
         * Policy di namespace App\Policies dengan nama konvensional
         * (DepositPolicy untuk model Deposit, dst) OTOMATIS terdeteksi
         * oleh Laravel — tidak memerlukan registrasi manual.
         */

        /*
         * Kebijakan kekuatan kata sandi global (dipakai Rules\Password::defaults()
         * di registrasi & ganti kata sandi): minimal 8 karakter dengan
         * kombinasi huruf besar, huruf kecil, dan angka.
         * Pakai closure agar mudah ditambah ->uncompromised(true) bila
         * cek pwned-passwords diinginkan (butuh koneksi internet server).
         */
        Password::defaults(fn (): Password => Password::min(8)
            ->mixedCase()
            ->numbers());
    }
}
