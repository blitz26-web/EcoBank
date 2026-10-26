<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DepositStatus;
use App\Enums\WithdrawalStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    private const LEVELS = [
        ['name' => 'Benih', 'min_points' => 0, 'icon' => 'bi-flower1', 'class' => 'text-secondary'],
        ['name' => 'Semai', 'min_points' => 500, 'icon' => 'bi-flower2', 'class' => 'text-success'],
        ['name' => 'Tunas', 'min_points' => 2000, 'icon' => 'bi-tree', 'class' => 'text-success'],
        ['name' => 'Rimbun', 'min_points' => 5000, 'icon' => 'bi-trees', 'class' => 'text-success'],
        ['name' => 'Lestari', 'min_points' => 10000, 'icon' => 'bi-globe-americas', 'class' => 'text-success'],
    ];

    private const BADGES = [
        ['key' => 'first_deposit', 'label' => 'Langkah Pertama', 'icon' => 'bi-star', 'hint' => 'Selesaikan satu setoran.'],
        ['key' => 'collector', 'label' => 'Kolektor', 'icon' => 'bi-collection', 'hint' => 'Selesaikan 10 setoran.'],
        ['key' => 'recycler', 'label' => 'Ahli Daur Ulang', 'icon' => 'bi-recycle', 'hint' => 'Selesaikan 25 setoran.'],
        ['key' => 'saver', 'label' => 'Penabung', 'icon' => 'bi-piggy-bank', 'hint' => 'Simpan 1.000 poin di saldo.'],
        ['key' => 'harvest', 'label' => 'Panen Pertama', 'icon' => 'bi-cash-coin', 'hint' => 'Cairkan penarikan pertama.'],
        ['key' => 'loyal', 'label' => 'Setia Sebulan', 'icon' => 'bi-fire', 'hint' => 'Setor minimal 4 minggu berturut-turut.'],
    ];

    public function levelFor(User $user): array
    {
        $points = $this->lifetimePoints($user);
        $index = 0;

        foreach (self::LEVELS as $i => $level) {
            if ($points >= $level['min_points']) {
                $index = $i;
            }
        }

        $current = self::LEVELS[$index];
        $next = self::LEVELS[$index + 1] ?? null;

        $progress = 100;
        if ($next !== null) {
            $span = $next['min_points'] - $current['min_points'];
            $progress = $span > 0
                ? (int) floor((($points - $current['min_points']) / $span) * 100)
                : 100;
        }

        return [
            'name' => $current['name'],
            'icon' => $current['icon'],
            'class' => $current['class'],
            'points' => $points,
            'next_name' => $next['name'] ?? null,
            'next_points' => $next['min_points'] ?? null,
            'progress' => min(100, max(0, $progress)),
        ];
    }

    public function badgesFor(User $user): array
    {
        $completedDeposits = $user->deposits()
            ->where('status', DepositStatus::Completed->value)
            ->count();

        $processedWithdrawals = $user->withdrawals()
            ->where('status', WithdrawalStatus::Processed->value)
            ->count();

        $streak = $this->weeklyStreak($user);

        $earned = [
            'first_deposit' => $completedDeposits >= 1,
            'collector' => $completedDeposits >= 10,
            'recycler' => $completedDeposits >= 25,
            'saver' => $user->balance_points >= 1000,
            'harvest' => $processedWithdrawals >= 1,
            'loyal' => $streak >= 4,
        ];

        return array_map(
            fn (array $badge): array => [...$badge, 'earned' => $earned[$badge['key']]],
            self::BADGES,
        );
    }

    public function weeklyStreak(User $user): int
    {
        $weeks = $user->deposits()
            ->where('status', DepositStatus::Completed->value)
            ->whereNotNull('created_at')
            ->selectRaw("DISTINCT DATE_FORMAT(created_at, '%x-%v') as week_key")
            ->orderByDesc('week_key')
            ->pluck('week_key');

        if ($weeks->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $cursor = now();

        if ($weeks->first() !== $cursor->format('o-W')) {
            $cursor = $cursor->subWeek();
        }

        while (in_array($cursor->format('o-W'), $weeks->all(), true)) {
            $streak++;
            $cursor = $cursor->subWeek();
        }

        return $streak;
    }

    public function lifetimePoints(User $user): int
    {
        return (int) DB::table('deposit_items')
            ->join('deposits', 'deposits.id', '=', 'deposit_items.deposit_id')
            ->where('deposits.user_id', $user->getKey())
            ->where('deposits.status', DepositStatus::Completed->value)
            ->sum('deposit_items.points_earned');
    }
}
