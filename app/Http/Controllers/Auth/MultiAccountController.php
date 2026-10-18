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

/**
 * Dukungan multi-akun login dalam satu sesi browser.
 *
 * Pola Google/Gmail:
 *  1. Tambah akun  → wajib memasukkan email + kata sandi akun tujuan
 *     (memakai LoginRequest sehingga menikmati rate limiter yang sama
 *     dengan halaman login: 5 percobaan/menit per email+IP).
 *  2. Pindah akun  → instan tanpa kredensial, hanya untuk akun yang
 *     terdaftar pada sesi ini.
 *  3. Keluar semua → akhiri seluruh sesi.
 *
 * Catatan keamanan: berpindah akun tidak meminta ulang kata sandi karena
 * seluruh akun dalam daftar dianggap sudah terautentikasi pada perangkat
 * yang sama. Daftar hanya menyimpan ID user (bukan kredensial) dan
 * dibersihkan saat logout semua / sesi berakhir.
 */
class MultiAccountController extends Controller
{
    /**
     * Halaman "Tambah akun": form kredensial akun lain.
     */
    public function create(Request $request): View
    {
        return view('auth.add-account', [
            'activeAccount' => Auth::user(),
            'accounts' => MultiAccountSession::users($request),
        ]);
    }

    /**
     * Autentikasi akun lain lalu jadikan akun aktif.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Simpan id akun aktif sekarang agar tetap terdaftar di daftar.
        $previousId = Auth::id();

        $request->authenticate();

        // Regenerasi session ID (data sesi lama tetap, id sesi baru).
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

    /**
     * Pindah ke salah satu akun yang sudah terdaftar pada sesi ini.
     */
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

        // users() hanya mengembalikan akun yang terdaftar di sesi INI
        // dan masih ada di database → pelanggaran/pembajakan id ditolak.
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

    /**
     * Keluar dari SEMUA akun pada sesi ini (sesi diakhiri sepenuhnya).
     */
    public function logoutAll(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // invalidate() menghapus seluruh data sesi, termasuk daftar multi-akun.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('status', 'Semua akun telah keluar dari Kas Hijau.');
    }
}
