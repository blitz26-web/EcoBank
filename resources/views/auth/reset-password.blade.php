@extends('layouts.guest')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
    <div class="card guest-card">
        <div class="card-body p-4 p-sm-5">
            <h2 class="h4 fw-bold mb-1">Atur ulang kata sandi</h2>
            <p class="text-muted small mb-4">Buat kata sandi baru untuk akun Anda.</p>

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf

                {{-- Token dari tautan email --}}
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $request->query('email')) }}"
                               placeholder="nama@contoh.com" required autofocus>
                    </div>
                    @error('email')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata Sandi Baru</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required autocomplete="new-password">
                    </div>
                    @error('password')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">Ulangi Kata Sandi Baru</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-key-fill"></i></span>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation"
                               required autocomplete="new-password">
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-shield-check me-1"></i>Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer bg-body border-0 text-center py-3 rounded-bottom-4">
            <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke halaman masuk
            </a>
        </div>
    </div>
@endsection
