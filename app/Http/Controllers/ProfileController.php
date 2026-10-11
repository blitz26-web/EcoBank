<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Pengelolaan profil akun: data diri dan penggantian kata sandi.
 */
class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Perbarui data profil (nama, email, telepon).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', Rule::unique(User::class)->ignore($user->getKey())],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ], [
            'email.unique' => 'Email sudah dipakai akun lain.',
            'email.lowercase' => 'Email harus ditulis huruf kecil.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka dan karakter + - ( ) spasi.',
        ]);

        $user->fill([
            'name' => trim($validated['name']),
            'email' => $validated['email'],
            'phone' => trim($validated['phone']),
        ])->save();

        return back()->with('status', 'profil-updated');
    }

    /**
     * Ganti kata sandi (wajib memasukkan kata sandi lama).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ], [
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return back()->with('status', 'password-updated');
    }
}
