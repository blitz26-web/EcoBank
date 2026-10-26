<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\MultiAccountSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MultiAccountController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.add-account', [
            'activeAccount' => Auth::user(),
            'accounts' => MultiAccountSession::users($request),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $previousId = Auth::id();

        $request->authenticate();

        $request->session()->regenerate();

        if ($previousId !== null && (int) $previousId !== (int) Auth::id()) {
            MultiAccountSession::remember($request, (int) $previousId);
        }

        MultiAccountSession::remember($request, (int) Auth::id());

        $user = Auth::user();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Akun ditambahkan. Kini aktif sebagai '.$user->name.'.');
    }

    public function switchAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
        ], [
            'user_id.required' => 'Akun tujuan belum dipilih.',
            'user_id.integer' => 'Akun tujuan tidak valid.',
        ]);

        $targetId = (int) $validated['user_id'];

        if ($targetId === (int) Auth::id()) {
            return redirect()->route('dashboard');
        }

        $target = MultiAccountSession::users($request)->firstWhere('id', $targetId);

        if ($target === null) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Akun tujuan tidak terdaftar pada sesi ini. Silakan tambahkan akun terlebih dahulu.');
        }

        Auth::login($target);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Berpindah ke akun '.$target->name.'.');
    }

    public function logoutAll(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('status', 'Semua akun telah keluar dari Kas Hijau.');
    }
}
