<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Deposit;
use App\Models\Withdrawal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Antrian kerja petugas (Langkah 4).
 *
 * Satu halaman dengan dua daftar berpaginasi:
 *  1. Setoran Pending  -> tombol verifikasi (kredit poin).
 *  2. Penarikan Requested -> tombol proses (selesaikan) / tolak (refund).
 *
 * Route dilindungi middleware `role:petugas,admin`. Dua paginator pada
 * satu halaman memakai pageName berbeda (setoran_page, penarikan_page)
 * agar membuka halaman daftar A tidak mereset posisi halaman daftar B.
 */
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
