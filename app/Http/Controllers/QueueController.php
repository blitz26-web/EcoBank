<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $pendingDeposits = Deposit::query()
            ->with(['user', 'items.wasteCategory'])
            ->where('status', DepositStatus::Pending->value)
            ->latest()
            ->paginate(10, pageName: 'setoran_page')
            ->withQueryString();

        $requestedWithdrawals = Withdrawal::query()
            ->with('user')
            ->where('status', WithdrawalStatus::Requested->value)
            ->latest()
            ->paginate(10, pageName: 'penarikan_page')
            ->withQueryString();

        return view('officer.queue', [
            'pendingDeposits' => $pendingDeposits,
            'requestedWithdrawals' => $requestedWithdrawals,
        ]);
    }
}
