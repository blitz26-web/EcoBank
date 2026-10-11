<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

/**
 * Aturan akses pengajuan penarikan poin.
 */
class WithdrawalPolicy
{
    /**
     * Melihat daftar: semua peran boleh, TAPI isi daftar wajib dibatasi
     * di controller (warga hanya melihat miliknya, petugas/admin semua).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Warga hanya melihat pengajuannya sendiri;
     * petugas melihat semua untuk antrian pemrosesan.
     */
    public function view(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isPetugas() || $user->id === $withdrawal->user_id;
    }

    /**
     * Hanya warga yang boleh mengajukan penarikan saldo.
     * Dipakai oleh StoreWithdrawalRequest::authorize().
     */
    public function create(User $user): bool
    {
        return $user->isWarga();
    }

    /**
     * Memproses pembayaran hanya untuk pengajuan berstatus requested.
     * Dipakai sebelum memanggil WithdrawalService::processWithdrawal().
     */
    public function process(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isPetugas() && $withdrawal->isRequested();
    }

    /**
     * Menolak pengajuan hanya untuk status requested.
     * Dipakai sebelum memanggil WithdrawalService::rejectWithdrawal().
     */
    public function reject(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isPetugas() && $withdrawal->isRequested();
    }
}
