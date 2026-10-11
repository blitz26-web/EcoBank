@extends('layouts.guest')

@section('title', 'Verifikasi Email')

@section('content')
    <div class="card guest-card">
        <div class="card-body p-4 p-sm-5 text-center">
            <span class="brand-icon mb-3" style="background: #ffc107; color: #212529;">
                <i class="bi bi-envelope-check"></i>
            </span>
            <h2 class="h4 fw-bold mb-2">Cek Kotak Masuk Anda</h2>
            <p class="text-muted small mb-4">
                Kami mengirim tautan verifikasi ke
                <span class="fw-semibold text-body">{{ auth()->user()->email }}</span>.
                Klik tautan tersebut untuk mengaktifkan akun Anda.
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success small py-2">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Tautan verifikasi baru telah dikirim ke email Anda.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-arrow-repeat me-1"></i>Kirim Ulang Tautan Verifikasi
                    </button>
                </div>
            </form>

            <div class="small text-muted">
                Email salah / ingin ganti akun?
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0 align-baseline">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
