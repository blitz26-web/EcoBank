<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Deposit;
use App\Models\User;

class DepositPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Deposit $deposit): bool
    {
        return $user->isPetugas() || $user->id === $deposit->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isWarga() || $user->isPetugas();
    }

    public function verify(User $user, Deposit $deposit): bool
    {
        return $user->isPetugas() && $deposit->isPending();
    }

    public function cancel(User $user, Deposit $deposit): bool
    {
        return $deposit->isPending() && $user->id === $deposit->user_id;
    }
}
