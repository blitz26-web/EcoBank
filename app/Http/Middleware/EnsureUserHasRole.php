<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware proteksi route berdasarkan kolom `users.role`.
 *
 * Pemakaian di route:
 *   Route::get('/antrian', ...)->middleware('role:petugas,admin');
 *   Route::get('/kelola',  ...)->middleware('role:admin');
 *
 * Catatan desain:
 * - Admin dianggap super user (selaras dengan Gate::before() di AppServiceProvider)
 *   dan selalu diloloskan middleware ini tanpa melihat daftar peran.
 * - Middleware ini HANYA memeriksa peran (coarse-grained), bukan permission
 *   granular. Untuk otorisasi pada level objek (mis. "warga hanya boleh
 *   melihat penarikan miliknya sendiri"), tetap gunakan Policy.
 */
class EnsureUserHasRole
{
    /**
     * @param  string ...$roles  Daftar peran yang diizinkan (nilai enum UserRole: warga|petugas|admin).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            // Route tanpa middleware `auth` di depannya: paksa ke login.
            return redirect()->route('login');
        }

        // Admin = super user (selaras Gate::before): lolos semua route ber-role.
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Normalisasi parameter ke enum; nilai asing dianggap tidak diizinkan.
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
