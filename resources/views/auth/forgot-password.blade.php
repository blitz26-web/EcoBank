@extends('layouts.guest')

@section('title', 'Lupa Kata Sandi')

@section('content')
    <div class="card guest-card">
        <div class="card-body p-4 p-sm-5">
            <h2 class="h4 fw-bold mb-1">Lupa kata sandi?</h2>
            <p class="text-muted small mb-4">
                Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi.
            </p>

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email') }}"
                               placeholder="nama@contoh.com" required autofocus>
                    </div>
                    @error('email')
                        <div class="form-text text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-send me-1"></i>Kirim Tautan Reset
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
