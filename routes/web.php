<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\PointLedgerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\WasteCategoryController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Web Kas Hijau
|--------------------------------------------------------------------------
| Kelompok route publik, terautentikasi, transaksi, dan terproteksi peran.
| Routes auth (login/register/reset password) dijalankan dari routes/auth.php.
| Nama route memakai konvensi Inggris, URL memakai bahasa Indonesia.
*/

/* ---------------------------- Publik ---------------------------- */

Route::view('/', 'welcome')->name('home');

/* ---------------------------- Terautentikasi ----------------------------
 * Verifikasi email dinonaktifkan sesuai permintaan (panduan aktivasi
 * ulang ada di README bagian "Verifikasi Email - Status Nonaktif"):
 * grup ini hanya memakai middleware `auth` sehingga akun baru langsung
 * dapat memakai aplikasi.
 */
Route::middleware(['auth'])->group(function (): void {
    // Dashboard utama: konten menyesuaikan peran (warga/petugas/admin).
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil & penggantian kata sandi.
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    /* ------------------ Setoran (semua peran) ------------------
     * Scoping + otorisasi ditangani di controller/policy:
     *   - warga          : melihat setoran miliknya, boleh mengajukan
     *                      setoran mandiri (Pending) & membatalkannya.
     *   - petugas/admin  : melihat semua, mencatat setoran loket
     *                      (Completed) & memverifikasi pengajuan.
     */
    Route::get('/setoran', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('/setoran/buat', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('/setoran', [DepositController::class, 'store'])->name('deposits.store');
    Route::delete('/setoran/{deposit}', [DepositController::class, 'cancel'])->name('deposits.cancel');

    /* ------------------ Penarikan (semua peran) ------------------
     * Pengajuan hanya warga (WithdrawalPolicy::create); proses/tolak
     * ada di grup petugas di bawah.
     */
    Route::get('/penarikan', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::get('/penarikan/ajukan', [WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/penarikan', [WithdrawalController::class, 'store'])->name('withdrawals.store');

    // Riwayat mutasi poin milik user yang login.
    Route::get('/riwayat-poin', [PointLedgerController::class, 'index'])->name('points.index');

    /* ------------------ Proteksi peran: petugas & admin ------------------
     * Antrian kerja harian: verifikasi setoran pending dan
     * memproses/menolak pengajuan penarikan.
     */
    Route::middleware('role:petugas,admin')->group(function (): void {
        Route::get('/antrian', QueueController::class)->name('officer.queue');
        Route::post('/setoran/{deposit}/verifikasi', [DepositController::class, 'verify'])->name('deposits.verify');
        Route::post('/penarikan/{withdrawal}/proses', [WithdrawalController::class, 'process'])->name('withdrawals.process');
        Route::post('/penarikan/{withdrawal}/tolak', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');
    });

    /* ------------------ Proteksi peran: admin ------------------
     * Ringkasan global dan CRUD master data kategori sampah.
     */
    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.overview');

        Route::get('/kategori-sampah', [WasteCategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori-sampah', [WasteCategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori-sampah/{kategori}', [WasteCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori-sampah/{kategori}', [WasteCategoryController::class, 'destroy'])->name('categories.destroy');
    });
});

require __DIR__.'/auth.php';
