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

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
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

    public function updatePassword(Request $request): RedirectResponse
    {
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

    public function updateGoal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'savings_goal_title' => ['nullable', 'string', 'max:100'],
            'savings_goal_points' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ], [
            'savings_goal_title.max' => 'Nama target maksimal 100 karakter.',
            'savings_goal_points.integer' => 'Target poin harus berupa angka.',
            'savings_goal_points.min' => 'Target poin minimal 1.',
            'savings_goal_points.max' => 'Target poin maksimal 1.000.000.',
        ], [
            'savings_goal_title' => 'nama target',
            'savings_goal_points' => 'target poin',
        ]);

        $hasPoints = isset($validated['savings_goal_points']) && $validated['savings_goal_points'] > 0;
        $title = trim((string) ($validated['savings_goal_title'] ?? ''));

        if ($hasPoints && $title === '') {
            return back()->withErrors(['savings_goal_title' => 'Isi nama target terlebih dahulu.'])->withInput();
        }

        $request->user()->forceFill([
            'savings_goal_title' => $hasPoints ? $title : null,
            'savings_goal_points' => $hasPoints ? (int) $validated['savings_goal_points'] : null,
        ])->save();

        return back()->with('status', 'goal-updated');
    }
}
