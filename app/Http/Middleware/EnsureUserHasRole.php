<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $allowed = array_filter(
            array_map(
                static fn (string $role): ?UserRole => UserRole::tryFrom($role),
                $roles,
            ),
            static fn (?UserRole $role): bool => $role !== null,
        );

        if ($allowed === [] || ! in_array($user->role, $allowed, true)) {
            abort(403, sprintf(
                'Akses ditolak. Halaman ini hanya untuk peran: %s.',
                implode(', ', array_map(
                    static fn (UserRole $role): string => $role->label(),
                    $allowed,
                )),
            ));
        }

        return $next($request);
    }
}
