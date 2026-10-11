<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Mengirim tautan reset kata sandi ke email pengguna.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan halaman permintaan tautan reset.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset kata sandi. Pesan status yang sama dikembalikan
     * baik email terkirim maupun tidak, agar tidak membocorkan
     * keberadaan akun (reconnaissance protection).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        // Pesan status tidak dibedakan antara email ada / tidak ada.
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::ResetLinkSent
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
