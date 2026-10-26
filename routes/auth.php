<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\MultiAccountController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/daftar', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('/daftar', [RegisteredUserController::class, 'store']);

    Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/masuk', [AuthenticatedSessionController::class, 'store']);

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

Route::middleware('auth')->group(function (): void {
    Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('/akun/tambah', [MultiAccountController::class, 'create'])
        ->name('accounts.create');

    Route::post('/akun/tambah', [MultiAccountController::class, 'store'])
        ->name('accounts.store');

    Route::post('/akun/pindah', [MultiAccountController::class, 'switchAccount'])
        ->name('accounts.switch');

    Route::post('/akun/keluar-semua', [MultiAccountController::class, 'logoutAll'])
        ->name('accounts.logoutAll');
});
