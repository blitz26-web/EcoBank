<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menampilkan halaman "silakan cek email Anda" untuk pengguna yang
 * belum memverifikasi alamat email.
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Pengguna yang sudah terverifikasi langsung diarahkan ke dashboard;
     * sisanya melihat halaman instruksi verifikasi.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard', absolute: false))
            : view('auth.verify-email');
    }
}
