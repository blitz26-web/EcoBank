<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Memproses tautan verifikasi email dari tombol di pesan email.
 *
 * EmailVerificationRequest (bawaan framework) memvalidasi tanda tangan
 * URL, ID, dan hash email sebelum method ini dijalankan - tautan
 * palsu/diubah otomatis ditolak 403.
 */
class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->fulfill()) {
            event(new Verified($request->user()));
        }

        return redirect()
            ->intended(route('dashboard', absolute: false).'?verified=1')
            ->with('status', 'email-verified');
    }
}
