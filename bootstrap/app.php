<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Bootstrap Aplikasi Kas Hijau (Laravel 11/12/13 style)
|--------------------------------------------------------------------------
| File ini menggantikan Kernel.php pada skeleton Laravel modern.
|
| PENTING: Jika project Anda SUDAH memiliki bootstrap/app.php sendiri,
| jangan menimpanya mentah-mentah. Cukup salin bagian berikut ke dalamnya:
|
|   1. $middleware->alias(['role' => EnsureUserHasRole::class]);
|   2. $middleware->redirectGuestsTo(fn () => route('login'));
|   3. $middleware->redirectUsersTo(fn () => route('dashboard'));
|
| Alias `auth` dan `guest` sudah tersedia bawaan framework.
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware khusus Kas Hijau: proteksi route per peran.
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Tamu yang belum login diarahkan ke halaman login.
        $middleware->redirectGuestsTo(fn () => route('login'));

        // User yang sudah login membuka halaman guest (login/register)
        // diarahkan ke dashboard.
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
