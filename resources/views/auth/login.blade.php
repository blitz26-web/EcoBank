@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="card guest-card">
        <div class="card-body p-4 p-sm-5">
            <h2 class="h4 fw-bold mb-1">Selamat datang kembali</h2>
            <p class="text-muted small mb-4">Masuk untuk mengelola poin sampah Anda.</p>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="nama@contoh.com" required autofocus autocomplete="username">
                    </div>
                    @error('email')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required autocomplete="current-password">
                    </div>
                    @error('password')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('password.request') }}" class="small text-decoration-none">
                        Lupa kata sandi?
                    </a>
                </div>
            </form>
        </div>
        <div class="card-footer bg-body border-0 text-center py-3 rounded-bottom-4">
            <span class="text-muted small">Belum punya akun?</span>
            <a href="{{ route('register') }}" class="small fw-semibold text-decoration-none">Daftar sekarang</a>
        </div>
    </div>
@endsection
