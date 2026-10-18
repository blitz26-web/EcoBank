<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Daftar akun yang pernah masuk pada satu sesi browser (dukungan multi-akun).
 *
 * Pola Google/Gmail: satu sesi, banyak akun — pengguna menambahkan akun
 * lain dengan memasukkan kredensialnya, lalu berpindah antar akun tanpa
 * mengisi ulang email/kata sandi.
 *
 * Keputusan desain:
 *  - Hanya ID user yang disimpan di sesi (tidak pernah kredensial).
 *  - Akun AKTIF tetap ditangani guard `web` bawaan Laravel, sehingga
 *    middleware `auth`, `role:...`, policy, dan scoping controller
 *    bekerja tanpa penyesuaian apa pun saat berpindah akun.
 *  - Daftar otomatis bersih saat logout semua, session invalidate,
 *    atau sesi kedaluwarsa.
 */
final class MultiAccountSession
{
    /** Kunci sesi yang menyimpan daftar ID akun. */
    public const SESSION_KEY = 'multi_accounts';

    /**
     * Ambil daftar ID akun yang tersimpan (termasuk akun aktif).
     *
     * @return list<int>
     */
    public static function ids(Request $request): array
    {
        $raw = $request->session()->get(self::SESSION_KEY, []);

        if (! is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $value) {
            $id = (int) $value;
            if ($id > 0 && ! in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * Catat sebuah akun ke daftar sesi (diabaikan bila sudah terdaftar).
     */
    public static function remember(Request $request, int $userId): void
    {
        $ids = self::ids($request);

        if (in_array($userId, $ids, true)) {
            return;
        }

        $ids[] = $userId;

        self::sync($request, $ids);
    }

    /**
     * Hapus satu akun dari daftar sesi.
     */
    public static function forget(Request $request, int $userId): void
    {
        $ids = array_values(array_filter(
            self::ids($request),
            static fn (int $id): bool => $id !== $userId,
        ));

        self::sync($request, $ids);
    }

    /**
     * Tulis ulang daftar akun ke sesi (kosong = hapus kunci).
     *
     * @param list<int> $ids
     */
    public static function sync(Request $request, array $ids): void
    {
        if ($ids === []) {
            $request->session()->forget(self::SESSION_KEY);

            return;
        }

        $clean = array_values(array_unique(array_map(
            static fn (int|string $value): int => (int) $value,
            $ids,
        )));

        $request->session()->put(self::SESSION_KEY, $clean);
    }

    /**
     * Model User untuk seluruh akun terdaftar yang masih ada di database.
     *
     * ID yang tidak lagi ditemukan (akun dihapus) otomatis tidak ikut.
     *
     * @return Collection<int, User>
     */
    public static function users(Request $request): Collection
    {
        $ids = self::ids($request);

        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }
}
