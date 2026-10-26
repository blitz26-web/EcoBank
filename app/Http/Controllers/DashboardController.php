<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Enums\UserRole;
use App\Enums\WithdrawalStatus;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\GamificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin() => $this->renderAdmin(),
            $user->isPetugas() => $this->renderPetugas(),
            default => $this->renderWarga($user),
        };
    }

    public function petugas(Request $request): View
    {
        return $this->renderPetugas();
    }

    public function admin(Request $request): View
    {
        return $this->renderAdminOverview();
    }

    private function trendSeries(?int $userId): array
    {
        $query = DB::table('deposit_items')
            ->join('deposits', 'deposits.id', '=', 'deposit_items.deposit_id')
            ->where('deposits.status', DepositStatus::Completed->value)
            ->whereBetween('deposits.created_at', [
                now()->subDays(29)->startOfDay(),
                now()->endOfDay(),
            ]);

        if ($userId !== null) {
            $query->where('deposits.user_id', $userId);
        }

        $rows = $query
            ->selectRaw('DATE(deposits.created_at) as day')
            ->selectRaw('SUM(deposit_items.points_earned) as points')
            ->groupBy('day')
            ->pluck('points', 'day');

        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $labels[] = $day->translatedFormat('d M');
            $data[] = (int) ($rows[$day->format('Y-m-d')] ?? 0);
        }

        return [$labels, $data];
    }

    private function categorySeries(?int $userId): array
    {
        $query = DB::table('deposit_items')
            ->join('deposits', 'deposits.id', '=', 'deposit_items.deposit_id')
            ->join('waste_categories', 'waste_categories.id', '=', 'deposit_items.waste_category_id')
            ->where('deposits.status', DepositStatus::Completed->value);

        if ($userId !== null) {
            $query->where('deposits.user_id', $userId);
        }

        $rows = $query
            ->selectRaw('waste_categories.name as name')
            ->selectRaw('SUM(deposit_items.points_earned) as points')
            ->groupBy('waste_categories.name')
            ->orderByDesc('points')
            ->get();

        return [
            $rows->pluck('name')->all(),
            $rows->pluck('points')->map(fn ($points) => (int) $points)->all(),
        ];
    }

    private function renderWarga(User $user): View
    {
        $depositStats = $user->deposits()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(sprintf(
                "COALESCE(SUM(CASE WHEN status = '%s' THEN total_points ELSE 0 END), 0) as total_points",
                DepositStatus::Completed->value,
            ))
            ->first();

        $activeWithdrawal = $user->withdrawals()
            ->where('status', WithdrawalStatus::Requested->value)
            ->exists();

        $recentLedgers = $user->pointLedgers()
            ->latest()
            ->limit(5)
            ->get();

        [$trendLabels, $trendData] = $this->trendSeries((int) $user->getKey());
        [$categoryLabels, $categoryData] = $this->categorySeries((int) $user->getKey());

        $gamification = app(GamificationService::class);

        return view('dashboard.warga', [
            'user' => $user,
            'totalDeposits' => (int) $depositStats->total,
            'totalPoints' => (int) $depositStats->total_points,
            'hasActiveWithdrawal' => $activeWithdrawal,
            'recentLedgers' => $recentLedgers,
            'pointValue' => (int) config('kas-hijau.point_value', 0),
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'categoryLabels' => $categoryLabels,
            'categoryData' => $categoryData,
            'level' => $gamification->levelFor($user),
            'badges' => $gamification->badgesFor($user),
            'weeklyStreak' => $gamification->weeklyStreak($user),
        ]);
    }

    private function renderPetugas(): View
    {
        $pendingDeposits = Deposit::query()
            ->with(['user', 'items'])
            ->where('status', DepositStatus::Pending->value)
            ->latest()
            ->limit(10)
            ->get();

        $requestedWithdrawals = Withdrawal::query()
            ->with('user')
            ->where('status', WithdrawalStatus::Requested->value)
            ->latest()
            ->limit(10)
            ->get();

        $completedToday = Deposit::query()
            ->where('status', DepositStatus::Completed->value)
            ->whereDate('updated_at', today())
            ->count();

        return view('dashboard.petugas', [
            'pendingDeposits' => $pendingDeposits,
            'requestedWithdrawals' => $requestedWithdrawals,
            'completedToday' => $completedToday,
        ]);
    }

    private function renderAdmin(): View
    {
        $userCounts = User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $circulatingPoints = (int) User::query()->sum('balance_points');

        $depositsToday = Deposit::query()->whereDate('created_at', today())->count();
        $withdrawalsToday = Withdrawal::query()->whereDate('created_at', today())->count();

        [$trendLabels, $trendData] = $this->trendSeries(null);
        [$categoryLabels, $categoryData] = $this->categorySeries(null);

        $leaderboard = User::query()
            ->where('role', UserRole::Warga->value)
            ->join('point_ledgers', 'point_ledgers.user_id', '=', 'users.id')
            ->where('point_ledgers.type', 'credit')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.id, users.name, SUM(point_ledgers.amount) as total_earned')
            ->orderByDesc('total_earned')
            ->limit(5)
            ->get();

        return view('dashboard.admin', [
            'wargaCount' => (int) ($userCounts['warga'] ?? 0),
            'petugasCount' => (int) ($userCounts['petugas'] ?? 0),
            'adminCount' => (int) ($userCounts['admin'] ?? 0),
            'circulatingPoints' => $circulatingPoints,
            'depositsToday' => $depositsToday,
            'withdrawalsToday' => $withdrawalsToday,
            'pointValue' => (int) config('kas-hijau.point_value', 0),
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'categoryLabels' => $categoryLabels,
            'categoryData' => $categoryData,
            'leaderboard' => $leaderboard,
        ]);
    }

    private function renderAdminOverview(): View
    {
        $userCounts = User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $circulatingPoints = (int) User::query()->sum('balance_points');

        $depositsToday = Deposit::query()->whereDate('created_at', today())->count();
        $withdrawalsToday = Withdrawal::query()->whereDate('created_at', today())->count();

        $ledgerRekapSql = "COALESCE(SUM(CASE WHEN point_ledgers.type = 'credit' "
            . "THEN point_ledgers.amount ELSE -point_ledgers.amount END), 0)";

        $ledgerMismatches = User::query()
            ->select('users.id', 'users.name', 'users.balance_points')
            ->selectRaw(sprintf('%s as ledger_total', $ledgerRekapSql))
            ->leftJoin('point_ledgers', 'point_ledgers.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.balance_points')
            ->havingRaw(sprintf('users.balance_points <> %s', $ledgerRekapSql))
            ->orderBy('users.id')
            ->limit(5)
            ->get();

        $recentDeposits = Deposit::query()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $recentWithdrawals = Withdrawal::query()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $topWarga = User::query()
            ->where('role', UserRole::Warga->value)
            ->orderByDesc('balance_points')
            ->limit(5)
            ->get(['id', 'name', 'balance_points']);

        $maxWargaBalance = (int) ($topWarga->first()?->balance_points ?? 0);

        $pendingDepositCount = Deposit::query()
            ->where('status', DepositStatus::Pending->value)
            ->count();

        $requestedWithdrawalCount = Withdrawal::query()
            ->where('status', WithdrawalStatus::Requested->value)
            ->count();

        return view('dashboard.admin-overview', [
            'wargaCount' => (int) ($userCounts['warga'] ?? 0),
            'petugasCount' => (int) ($userCounts['petugas'] ?? 0),
            'adminCount' => (int) ($userCounts['admin'] ?? 0),
            'circulatingPoints' => $circulatingPoints,
            'depositsToday' => $depositsToday,
            'withdrawalsToday' => $withdrawalsToday,
            'pointValue' => (int) config('kas-hijau.point_value', 0),
            'reconciliationOk' => $ledgerMismatches->isEmpty(),
            'ledgerMismatches' => $ledgerMismatches,
            'recentDeposits' => $recentDeposits,
            'recentWithdrawals' => $recentWithdrawals,
            'topWarga' => $topWarga,
            'maxWargaBalance' => $maxWargaBalance,
            'pendingDepositCount' => $pendingDepositCount,
            'requestedWithdrawalCount' => $requestedWithdrawalCount,
        ]);
    }
}
