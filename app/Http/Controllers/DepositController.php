<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Http\Requests\StoreDepositRequest;
use App\Models\Deposit;
use App\Models\User;
use App\Models\WasteCategory;
use App\Services\DepositService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DepositController extends Controller
{
    public function __construct(private readonly DepositService $deposits)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $status = DepositStatus::tryFrom((string) $request->query('status', ''));

        $deposits = Deposit::query()
            ->with(['user', 'officer', 'items.wasteCategory'])
            ->when($user->isWarga(), fn ($query) => $query->where('user_id', $user->getKey()))
            ->when($status !== null, fn ($query) => $query->where('status', $status->value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('deposits.index', [
            'deposits' => $deposits,
            'statusFilter' => $status,
            'statuses' => DepositStatus::cases(),
            'isWarga' => $user->isWarga(),
            'canVerify' => $user->isPetugas() || $user->isAdmin(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $isCounterMode = ! $user->isWarga();

        return view('deposits.create', [
            'categories' => WasteCategory::query()->orderBy('name')->get(['id', 'name', 'unit', 'point_per_unit']),
            'wargaOptions' => $isCounterMode
                ? User::query()->where('role', 'warga')->orderBy('name')->get(['id', 'name', 'email'])
                : collect(),
            'isCounterMode' => $isCounterMode,
            'maxItems' => StoreDepositRequest::MAX_ITEMS,
            'pointValue' => (int) config('kas-hijau.point_value', 0),
            'oldItems' => collect((array) old('items', []))
                ->filter(fn ($item) => is_array($item) && ((string) ($item['waste_category_id'] ?? '') !== ''))
                ->values(),
        ]);
    }

    public function store(StoreDepositRequest $request): RedirectResponse
    {
        $user = $request->user();

        $items = array_map(
            static fn (array $item): array => [
                'waste_category_id' => (int) $item['waste_category_id'],
                'weight_in_kg' => (float) $item['weight_in_kg'],
            ],
            (array) $request->validated('items'),
        );

        if ($user->isWarga()) {
            $this->deposits->createDeposit($user, $items, null, DepositStatus::Pending);

            return redirect()
                ->route('deposits.index')
                ->with('status', 'deposit-submitted');
        }

        $holder = User::query()->findOrFail((int) $request->validated('user_id'));

        $this->deposits->createDeposit($holder, $items, $user, DepositStatus::Completed);

        return redirect()
            ->route('deposits.index')
            ->with('status', 'deposit-recorded');
    }

    public function verify(Request $request, Deposit $deposit): RedirectResponse
    {
        $this->authorize('verify', $deposit);

        try {
            $this->deposits->completeDeposit($deposit, $request->user());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'deposit-verified');
    }

    public function cancel(Request $request, Deposit $deposit): RedirectResponse
    {
        $this->authorize('cancel', $deposit);

        try {
            $this->deposits->cancelDeposit($deposit);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'deposit-cancelled');
    }
}
