<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class MultiAccountSession
{
    public const SESSION_KEY = 'multi_accounts';

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

    public static function remember(Request $request, int $userId): void
    {
        $ids = self::ids($request);

        if (in_array($userId, $ids, true)) {
            return;
        }

        $ids[] = $userId;

        self::sync($request, $ids);
    }

    public static function forget(Request $request, int $userId): void
    {
        $ids = array_values(array_filter(
            self::ids($request),
            static fn (int $id): bool => $id !== $userId,
        ));

        self::sync($request, $ids);
    }

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
