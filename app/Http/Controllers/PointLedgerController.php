<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\LedgerType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PointLedgerController extends Controller
{
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
