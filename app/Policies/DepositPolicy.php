<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deposit;
use App\Models\User;

/**
 * Aturan akses transaksi setoran.
 *
 * Catatan: admin otomatis lolos semua pemeriksaan berkat Gate::before()
 * di AppServiceProvider, sehingga method di sini cukup mengatur warga
 * dan petugas.
 */
class DepositPolicy
{
    /**
     * Melihat daftar: semua peran boleh, TAPI isi daftar wajib dibatasi
     * di controller (warga hanya melihat miliknya, petugas/admin melihat
     * semua) — pola scoping standar Laravel.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Warga hanya boleh melihat setoran miliknya sendiri;
     * petugas boleh melihat semua untuk keperluan verifikasi.
     */
    public function view(User $user, Deposit $deposit): bool
    {
        return $user->isPetugas() || $user->id === $deposit->user_id;
    }

    /**
     * Warga membuat pengajuan mandiri (pending);
     * petugas mencatat setoran langsung di loket.
     */
    public function create(User $user): bool
    {
        return $user->isWarga() || $user->isPetugas();
    }

    /**
     * Verifikasi hanya untuk setoran berstatus pending.
     * Dipakai sebelum memanggil DepositService::completeDeposit().
     */
    public function verify(User $user, Deposit $deposit): bool
    {
        return $user->isPetugas() && $deposit->isPending();
    }

    /**
     * Pembatalan hanya setoran pending, oleh pemiliknya sendiri.
     */
    public function cancel(User $user, Deposit $deposit): bool
    {
        return $deposit->isPending() && $user->id === $deposit->user_id;
    }
}
