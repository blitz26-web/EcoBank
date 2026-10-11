@extends('layouts.errors')

@section('title', 'Akses Ditolak (403)')

@section('content')
    <div class="error-code">403</div>
    <h1 class="h4 fw-bold mb-2">Akses Ditolak</h1>
    <p class="text-muted mb-4" style="max-width: 480px;">
        Maaf, Anda tidak memiliki izin untuk membuka halaman ini.
        Halaman tersebut hanya tersedia untuk peran tertentu
        (misalnya petugas atau administrator).
    </p>
    <a href="{{ url('/dashboard') }}" class="btn btn-success px-4">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
    </a>
@endsection
