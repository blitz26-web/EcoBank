<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $role = (string) $request->query('role', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when(in_array($role, ['warga', 'petugas', 'admin'], true), fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:warga,petugas'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone.required' => 'Nomor HP wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::query()->create([...$data, 'is_active' => true]);

        return redirect()->route('users.index')->with('status', 'user-created');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Peran akun Anda sendiri tidak dapat diubah.');
        }

        $data = $request->validate([
            'role' => ['required', 'in:warga,petugas,admin'],
        ]);

        $user->update($data);

        return back()->with('status', 'user-role-updated');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', 'user-status-updated');
    }
}
