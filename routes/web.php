<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PointLedgerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WasteCategoryController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profil/target', [ProfileController::class, 'updateGoal'])->name('profile.goal.update');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifikasi/{notification}/baca', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/setoran', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('/setoran/buat', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('/setoran', [DepositController::class, 'store'])->name('deposits.store');
    Route::delete('/setoran/{deposit}', [DepositController::class, 'cancel'])->name('deposits.cancel');

    Route::get('/penarikan', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/penarikan/ajukan', [WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/penarikan', [WithdrawalController::class, 'store'])->name('withdrawals.store');

    Route::get('/riwayat-poin', [PointLedgerController::class, 'index'])->name('points.index');

    Route::middleware('role:petugas,admin')->group(function (): void {
        Route::get('/antrian', QueueController::class)->name('officer.queue');
        Route::post('/setoran/{deposit}/verifikasi', [DepositController::class, 'verify'])->name('deposits.verify');
        Route::post('/penarikan/{withdrawal}/proses', [WithdrawalController::class, 'process'])->name('withdrawals.process');
        Route::post('/penarikan/{withdrawal}/tolak', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

        Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/unduh', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.overview');

        Route::get('/kategori-sampah', [WasteCategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori-sampah', [WasteCategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori-sampah/{kategori}', [WasteCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori-sampah/{kategori}', [WasteCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/pengguna', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/pengguna/buat', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/pengguna', [UserManagementController::class, 'store'])->name('users.store');
        Route::patch('/pengguna/{user}/peran', [UserManagementController::class, 'updateRole'])->name('users.role');
        Route::patch('/pengguna/{user}/status', [UserManagementController::class, 'toggleActive'])->name('users.toggle');
    });
});

require __DIR__.'/auth.php';
