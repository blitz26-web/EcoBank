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
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::before(function (User $user, string $ability): ?bool {
            return $user->isAdmin() ? true : null;
        });

        Password::defaults(fn (): Password => Password::min(8)
            ->mixedCase()
            ->numbers());
    }
}
