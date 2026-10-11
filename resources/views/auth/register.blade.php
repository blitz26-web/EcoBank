@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <div class="card guest-card">
        <div class="card-body p-4 p-sm-5">
            <h2 class="h4 fw-bold mb-1">Buat akun baru</h2>
            <p class="text-muted small mb-4">
                Akun baru otomatis berperan sebagai <span class="badge bg-success">Warga</span>.
                Akun petugas hanya dibuat oleh admin.
            </p>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="Nama Anda" required autofocus autocomplete="name">
                    </div>
                    @error('name')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="nama@contoh.com" required autocomplete="username">
                    </div>
                    @error('email')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label fw-semibold">No. Telepon</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-telephone"></i></span>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                               id="phone" name="phone" value="{{ old('phone') }}"
                               placeholder="08xx-xxxx-xxxx" required autocomplete="tel">
                    </div>
                    @error('phone')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" required autocomplete="new-password">
                    </div>
                    <div class="form-text">Minimal 8 karakter, kombinasi huruf dan angka lebih baik.</div>
                    @error('password')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">Ulangi Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-key-fill"></i></span>
                        <input type="password" class="form-control"
                               id="password_confirmation" name="password_confirmation"
                               required autocomplete="new-password">
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-person-plus me-1"></i>Daftar
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer bg-body border-0 text-center py-3 rounded-bottom-4">
            <span class="text-muted small">Sudah punya akun?</span>
            <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none">Masuk di sini</a>
        </div>
    </div>
@endsection
