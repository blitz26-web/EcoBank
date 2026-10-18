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

/**
 * Controller sesi login & logout (pola Breeze).
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login menggunakan LoginRequest (dengan rate limiting).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Regenerasi session ID untuk mencegah session fixation.
        $request->session()->regenerate();

        // Catat akun ini pada daftar multi-akun sesi (dukungan pindah akun).
        MultiAccountSession::remember($request, (int) Auth::id());

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Proses logout.
     *
     * Dukungan multi-akun: bila masih ada akun lain yang terdaftar pada
     * sesi ini, akun pertama tersebut langsung diaktifkan (tanpa login
     * ulang) — sesi TIDAK di-invalidate agar daftarnya bertahan.
     * Bila tidak ada akun tersisa, sesi diakhiri sepenuhnya.
     *
     * Untuk mengakhiri SEMUA akun sekaligus, gunakan accounts.logoutAll
     * (MultiAccountController::logoutAll).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $currentId = (int) (Auth::id() ?? 0);

        // Sisa akun lain pada sesi ini (akun aktif dikecualikan).
        $next = MultiAccountSession::users($request)
            ->reject(static fn (User $user): bool => (int) $user->id === $currentId)
            ->first();

        // Keluarkan akun aktif dari daftar multi-akun.
        MultiAccountSession::forget($request, $currentId);

        Auth::guard('web')->logout();

        if ($next !== null) {
            // Pastikan akun pengganti tetap terdaftar lalu aktifkan.
            MultiAccountSession::remember($request, (int) $next->id);
            Auth::guard('web')->login($next);

            // Cukup segarkan token CSRF; sesi TIDAK di-invalidate agar
            // daftar akun lain tetap utuh.
            $request->session()->regenerateToken();

            return redirect()
                ->route('dashboard')
                ->with('status', 'Keluar dari akun sebelumnya. Kini aktif sebagai '.$next->name.'.');
        }

        // Tidak ada akun tersisa: akhiri sesi sepenuhnya.
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda telah keluar dari Kas Hijau.');
    }
}
