<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Autentikasi (pola Breeze, Blade stack)
|--------------------------------------------------------------------------
| Route tamu (guest): login, daftar, lupa/reset kata sandi.
| Route terautentikasi: logout.
*/

/* ------------------------------ Registrasi ------------------------------ */

Route::middleware('guest')->group(function (): void {
    Route::get('/daftar', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/daftar', [RegisteredUserController::class, 'store']);

    /* ------------------------------- Login ------------------------------- */

    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/masuk', [AuthenticatedSessionController::class, 'store']);

    /* -------------------------- Reset kata sandi -------------------------
     * POST dilindungi throttle (6 permintaan/menit per IP) untuk mencegah
     * mail bombing pada endpoint publik pengirim email.
     */
    Route::get('/lupa-kata-sandi', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('/lupa-kata-sandi', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/atur-ulang-kata-sandi/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/atur-ulang-kata-sandi', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.update');
});

/* Verifikasi email dinonaktifkan sesuai permintaan (panduan aktivasi ulang
 * ada di README bagian "Verifikasi Email - Status Nonaktif"). */

/* ------------------------------- Logout --------------------------------- */

Route::middleware('auth')->group(function (): void {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
