<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Enums\UserRole;
use App\Enums\WithdrawalStatus;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard ringkas per peran (Langkah 3) + panel admin (revisi UI).
 *
 * Route `/dashboard` melayani semua peran: setelah login/register
 * pengguna diarahkan ke sini, lalu view yang dirender menyesuaikan
 * perannya (warga / petugas / admin).
 *
 * Route `/admin` (admin.overview, middleware role:admin) SENGAJA DIBEDAKAN
 * dari `/dashboard`: isinya panel lengkap khusus admin — semua statistik
 * ringkasan DITAMBAH rekonsiliasi ledger, transaksi terbaru, top saldo
 * warga, dan ringkasan antrian kerja. Dashboard admin tetap ringkas.
 */
class DashboardController extends Controller
{
    /**
     * Dashboard utama — konten mengikuti peran user yang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin() => $this->renderAdmin(),
            $user->isPetugas() => $this->renderPetugas(),
            default => $this->renderWarga($user),
        };
    }

    /**
     * Antrian petugas — dilindungi middleware `role:petugas,admin`.
     */
    public function petugas(Request $request): View
    {
        return $this->renderPetugas();
    }

    /**
     * Panel admin lengkap — dilindungi middleware `role:admin`.
     *
     * Sengaja BUKAN duplikat dashboard: berisi semua statistik ringkasan
     * plus data mendalam yang hanya relevan bagi admin (rekonsiliasi
     * ledger, transaksi terbaru lintas warga, distribusi saldo, antrian).
     */
    public function admin(Request $request): View
    {
        return $this->renderAdminOverview();
    }

    /* ------------------------------------------------------------------ */
    /* Private render helpers                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Dashboard warga: kartu saldo poin + nilai Rupiah, statistik
     * setoran & penarikan, dan 5 mutasi poin terakhir.
     */
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

        return view('dashboard.warga', [
            'user' => $user,
            'totalDeposits' => (int) $depositStats->total,
            'totalPoints' => (int) $depositStats->total_points,
            'hasActiveWithdrawal' => $activeWithdrawal,
            'recentLedgers' => $recentLedgers,
            'pointValue' => (int) config('kas-hijau.point_value', 0),
        ]);
    }

    /**
     * Antrian petugas: setoran pending menunggu verifikasi dan
     * penarikan requested menunggu diproses/ditolak.
     */
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

    /**
     * Ringkasan admin: statistik user, poin beredar, aktivitas hari ini.
     */
    private function renderAdmin(): View
    {
        $userCounts = User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $circulatingPoints = (int) User::query()->sum('balance_points');

        $depositsToday = Deposit::query()->whereDate('created_at', today())->count();
        $withdrawalsToday = Withdrawal::query()->whereDate('created_at', today())->count();

        return view('dashboard.admin', [
            'wargaCount' => (int) ($userCounts['warga'] ?? 0),
            'petugasCount' => (int) ($userCounts['petugas'] ?? 0),
            'adminCount' => (int) ($userCounts['admin'] ?? 0),
            'circulatingPoints' => $circulatingPoints,
            'depositsToday' => $depositsToday,
            'withdrawalsToday' => $withdrawalsToday,
            'pointValue' => (int) config('kas-hijau.point_value', 0),
        ]);
    }

    /**
     * Panel admin lengkap untuk route `/admin` (admin.overview).
     *
     * Menyediakan:
     *  1. Statistik pengguna & poin beredar (sama dengan dashboard ringkas).
     *  2. REKONSILIASI LEDGER — validasi invarian "saldo = rekap point_ledgers"
     *     per user langsung di database (HAVING). Bila ada selisih, baris
     *     user bermasalah ditampilkan untuk ditindaklanjuti.
     *  3. Transaksi terbaru (setoran & penarikan lintas semua warga).
     *  4. Distribusi saldo — 5 warga dengan saldo tertinggi.
     *  5. Ringkasan antrian kerja petugas (pending/requested) + CTA.
     */
    private function renderAdminOverview(): View
    {
        $userCounts = User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $circulatingPoints = (int) User::query()->sum('balance_points');

        $depositsToday = Deposit::query()->whereDate('created_at', today())->count();
        $withdrawalsToday = Withdrawal::query()->whereDate('created_at', today())->count();

        /* --- 2. Rekonsiliasi ledger (invarian saldo vs buku besar) --- */
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

        /* --- 3. Transaksi terbaru lintas warga --- */
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

        /* --- 4. Distribusi saldo: 5 warga teratas --- */
        $topWarga = User::query()
            ->where('role', UserRole::Warga->value)
            ->orderByDesc('balance_points')
            ->limit(5)
            ->get(['id', 'name', 'balance_points']);

        $maxWargaBalance = (int) ($topWarga->first()?->balance_points ?? 0);

        /* --- 5. Ringkasan antrian kerja petugas --- */
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
