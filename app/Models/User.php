<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model user EcoBank.
 *
 * Verifikasi email DINONAKTIFKAN sesuai permintaan: model ini tidak
 * mengimplementasikan MustVerifyEmail sehingga akun baru langsung dapat
 * memakai aplikasi tanpa tautan verifikasi. Kolom email_verified_at
 * beserta cast-nya tetap dipertahankan agar aktivasi ulang di masa depan
 * cukup menambahkan kembali interface MustVerifyEmail + route/view
 * verifikasi (panduan lengkap di README).
 * Reset kata sandi tetap dikirim berbahasa Indonesia lewat notifikasi
 * kustom di App\Notifications.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /*
     * Aktifkan trait berikut SETELAH menjalankan:
     * composer require spatie/laravel-permission
     * Trait ini diperlukan untuk manajemen permission granular,
     * sementara kolom `role` dipakai untuk filter/ pengecekan cepat.
     */
    // use \Spatie\Permission\Traits\HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'balance_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'balance_points' => 'integer',
        ];
    }

    /* ------------------------------------------------------------------ */
    /* Relasi                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Setoran yang diajukan oleh warga ini (sebagai pemilik rekening poin).
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'user_id');
    }

    /**
     * Setoran yang diverifikasi / dicatat oleh petugas ini.
     */
    public function processedDeposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'officer_id');
    }

    /**
     * Pengajuan penarikan saldo milik warga ini.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Riwayat mutasi poin (audit trail) milik user ini.
     */
    public function pointLedgers(): HasMany
    {
        return $this->hasMany(PointLedger::class);
    }

    /* ------------------------------------------------------------------ */
    /* Helper peran & saldo                                                */
    /* ------------------------------------------------------------------ */

    public function isWarga(): bool
    {
        return $this->role === UserRole::Warga;
    }

    public function isPetugas(): bool
    {
        return $this->role === UserRole::Petugas;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Cek apakah saldo poin mencukupi untuk pengambilan sebesar $amount.
     * Dipakai oleh WithdrawalService untuk menolak penarikan melebihi saldo.
     */
    public function hasSufficientPoints(int $amount): bool
    {
        return $this->balance_points >= $amount;
    }

    /* ------------------------------------------------------------------ */
    /* Notifikasi auth berbahasa Indonesia                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Kirim tautan atur ulang kata sandi memakai notifikasi kustom
     * berbahasa Indonesia.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
