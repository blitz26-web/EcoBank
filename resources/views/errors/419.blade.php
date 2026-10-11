@extends('layouts.errors')

@section('title', 'Sesi Berakhir (419)')

@section('content')
    <div class="error-code">419</div>
    <h1 class="h4 fw-bold mb-2">Sesi Telah Berakhir</h1>
    <p class="text-muted mb-4" style="max-width: 480px;">
        Halaman terlalu lama dibiarkan terbuka sehingga sesi keamanan berakhir.
        Silakan muat ulang halaman, lalu coba lagi.
    </p>
    <a href="javascript:location.reload()" class="btn btn-success px-4">
        <i class="bi bi-arrow-clockwise me-1"></i>Muat Ulang Halaman
    </a>
@endsection
