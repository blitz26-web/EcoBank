<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\MultiAccountSession;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.unique' => 'Email sudah terdaftar. Silakan masuk atau gunakan email lain.',
            'email.email' => 'Format email tidak valid.',
            'email.lowercase' => 'Email harus ditulis huruf kecil.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka dan karakter + - ( ) spasi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal :min karakter.',
        ]);

        $user = User::query()->create([
            'name' => trim($validated['name']),
            'email' => $validated['email'],
            'phone' => trim($validated['phone']),
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Warga,
            'balance_points' => 0,
        ]);

        event(new Registered($user));

        Auth::login($user);

        MultiAccountSession::remember($request, (int) $user->id);

        return redirect()->route('dashboard');
    }
}
