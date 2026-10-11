@extends('layouts.errors')

@section('title', 'Kesalahan Server (500)')

@section('content')
    <div class="error-code">500</div>
    <h1 class="h4 fw-bold mb-2">Terjadi Kesalahan Server</h1>
    <p class="text-muted mb-4" style="max-width: 480px;">
        Kami mohon maaf, terjadi kesalahan tak terduga di sisi server.
        Tim kami sudah/notasi kejadian ini. Silakan coba beberapa saat lagi.
    </p>
    <a href="{{ url('/') }}" class="btn btn-success px-4">
        <i class="bi bi-house me-1"></i>Kembali ke Beranda
    </a>
@endsection
