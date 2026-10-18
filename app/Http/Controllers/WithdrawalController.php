<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WithdrawalStatus;
use App\Http\Requests\RejectWithdrawalRequest;
use App\Http\Requests\StoreWithdrawalRequest;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

/**
 * Alur kerja penarikan poin (Langkah 4).
 *
 *  - index   : riwayat penarikan (warga: miliknya; petugas/admin: semua)
 *              dengan filter status + pagination.
 *  - create  : form pengajuan penarikan (hanya warga) dengan preview
 *              konversi poin ke Rupiah dari config server.
 *  - store   : simpan via WithdrawalService — pola HOLD: poin di-debit
 *              saat pengajuan, saldo dicek di dalam transaksi + row lock
 *              (InsufficientPointsException otomatis render 422/redirect).
 *  - process : tandai penarikan sudah dibayar (petugas/admin).
 *  - reject  : tolak + refund poin otomatis dengan alasan wajib.
 */
class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawals)
    {
    }

    /**
     * Riwayat penarikan dengan filter status + pagination.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $status = WithdrawalStatus::tryFrom((string) $request->query('status', ''));

        $withdrawals = Withdrawal::query()
            ->with(['user', 'officer'])
            ->when($user->isWarga(), fn ($query) => $query->where('user_id', $user->getKey()))
            ->when($status !== null, fn ($query) => $query->where('status', $status->value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('withdrawals.index', [
            'withdrawals' => $withdrawals,
            'statusFilter' => $status,
            'statuses' => WithdrawalStatus::cases(),
            'isWarga' => $user->isWarga(),
            'canDecide' => $user->isPetugas() || $user->isAdmin(),
            'pointValue' => (int) config('kas-hijau.point_value', 0),
        ]);
    }

    /**
     * Form pengajuan penarikan poin — hanya warga.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Withdrawal::class);

        $user = $request->user();

        return view('withdrawals.create', [
            'balance' => (int) $user->balance_points,
            'minPoints' => max(1, (int) config('kas-hijau.min_withdrawal_points', 1)),
            'maxPoints' => (int) config('kas-hijau.max_withdrawal_points', 0),
            'pointValue' => (int) config('kas-hijau.point_value', 0),
            'paymentLabels' => Withdrawal::paymentLabels(),
            'hasActiveWithdrawal' => $user->withdrawals()
                ->where('status', WithdrawalStatus::Requested->value)
                ->exists(),
        ]);
    }

    /**
     * Simpan pengajuan penarikan (pola HOLD). Kegagalan saldo dilempar
     * sebagai InsufficientPointsException yang render() otomatis
     * mengembalikan user ke form dengan pesan error yang jelas.
     */
    public function store(StoreWithdrawalRequest $request): RedirectResponse
    {
        $this->withdrawals->requestWithdrawal(
            $request->user(),
            (int) $request->validated('points_amount'),
            (string) $request->validated('payment_method'),
            $request->validated('account_number'),
        );

        return redirect()
            ->route('withdrawals.index')
            ->with('status', 'withdrawal-submitted');
    }

    /**
     * Tandai penarikan selesai dibayar (petugas/admin).
     */
    public function process(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        Gate::authorize('process', $withdrawal);

        try {
            $this->withdrawals->processWithdrawal($withdrawal, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'withdrawal-processed');
    }

    /**
     * Tolak penarikan + pengembalian poin (petugas/admin).
     */
    public function reject(RejectWithdrawalRequest $request, Withdrawal $withdrawal): RedirectResponse
    {
        Gate::authorize('reject', $withdrawal);

        try {
            $this->withdrawals->rejectWithdrawal(
                $withdrawal,
                $request->user(),
                (string) $request->validated('reason'),
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'withdrawal-rejected');
    }
}
