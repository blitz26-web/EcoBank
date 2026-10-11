@extends('layouts.errors')

@section('title', 'Halaman Tidak Ditemukan (404)')

@section('content')
    <div class="error-code">404</div>
    <h1 class="h4 fw-bold mb-2">Halaman Tidak Ditemukan</h1>
    <p class="text-muted mb-4" style="max-width: 480px;">
        Halaman yang Anda cari mungkin sudah dipindahkan, dihapus,
        atau alamat yang diketik tidak tepat.
    </p>
    <a href="{{ url('/') }}" class="btn btn-success px-4">
        <i class="bi bi-house me-1"></i>Kembali ke Beranda
    </a>
@endsection
