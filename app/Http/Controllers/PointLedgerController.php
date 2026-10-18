<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LedgerType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Riwayat mutasi poin (audit trail) milik user yang sedang login.
 *
 * Ledger bersifat imutabel: halaman ini HANYA membaca. Saldo terkini
 * selalu konsisten dengan rekap entri ledger (invarian Kas Hijau), dan
 * filter arah mutasi (masuk/keluar) memudahkan warga menelusuri
 * sumber poin maupun pengeluarannya.
 */
class PointLedgerController extends Controller
{
    /**
     * Riwayat mutasi poin milik user yang login (semua peran) dengan
     * filter arah mutasi + pagination.
     */
    public function index(Request $request): View
    {
        $type = LedgerType::tryFrom((string) $request->query('type', ''));

        $ledgers = $request->user()
            ->pointLedgers()
            ->with('reference')
            ->when($type !== null, fn ($query) => $query->where('type', $type->value))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('points.index', [
            'ledgers' => $ledgers,
            'typeFilter' => $type,
            'types' => LedgerType::cases(),
        ]);
    }
}
