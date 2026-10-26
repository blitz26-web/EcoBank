<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\MultiAccountSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        MultiAccountSession::remember($request, (int) Auth::id());

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $currentId = (int) (Auth::id() ?? 0);

        $next = MultiAccountSession::users($request)
            ->reject(static fn (User $user): bool => (int) $user->id === $currentId)
            ->first();

        MultiAccountSession::forget($request, $currentId);

        Auth::guard('web')->logout();

        if ($next !== null) {
            MultiAccountSession::remember($request, (int) $next->id);
            Auth::guard('web')->login($next);

            $request->session()->regenerateToken();

            return redirect()
                ->route('dashboard')
                ->with('status', 'Keluar dari akun sebelumnya. Kini aktif sebagai '.$next->name.'.');
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda telah keluar dari Kas Hijau.');
    }
}
