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

class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawals)
    {
    }

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
