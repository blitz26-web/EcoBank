@extends('layouts.app')

@section('title', 'Ringkasan Admin')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Ringkasan Sistem</h1>
            <p class="text-muted mb-0">Pantau pengguna, poin beredar, dan aktivitas harian.</p>
        </div>
        <span class="badge bg-{{ auth()->user()->role->badgeClass() }} fs-6 px-3 py-2">
            <i class="bi bi-person-badge me-1"></i>{{ auth()->user()->role->label() }}
        </span>
    </div>

    {{-- ==================== Statistik Pengguna ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-people"></i></span>
                    <div>
                        <div class="text-muted small">Warga</div>
                        <div class="fs-4 fw-bold">{{ number_format($wargaCount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-person-workspace"></i></span>
                    <div>
                        <div class="text-muted small">Petugas</div>
                        <div class="fs-4 fw-bold">{{ number_format($petugasCount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-shield-lock"></i></span>
                    <div>
                        <div class="text-muted small">Admin</div>
                        <div class="fs-4 fw-bold">{{ number_format($adminCount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-star-fill"></i></span>
                    <div>
                        <div class="text-muted small">Poin Beredar</div>
                        <div class="fs-4 fw-bold">{{ number_format($circulatingPoints, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ==================== Aktivitas Hari Ini ==================== --}}
        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-activity me-1 text-success"></i>Aktivitas Hari Ini</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <div class="fw-semibold">Setoran dibuat</div>
                            <div class="text-muted small">Seluruh status (pending, selesai, batal)</div>
                        </div>
                        <span class="fs-3 fw-bold text-success">{{ number_format($depositsToday, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Penarikan diajukan</div>
                            <div class="text-muted small">Seluruh status (menunggu, diproses, ditolak)</div>
                        </div>
                        <span class="fs-3 fw-bold text-primary">{{ number_format($withdrawalsToday, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== Konfigurasi ==================== --}}
        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-gear me-1 text-success"></i>Konfigurasi Nilai Tukar</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <div class="fw-semibold">1 poin = Rp {{ number_format($pointValue, 0, ',', '.') }}</div>
                            <div class="text-muted small">Atur lewat <code>KAS_HIJAU_POINT_VALUE</code> di file .env</div>
                        </div>
                        <i class="bi bi-percent fs-3 text-muted"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Nilai poin beredar</div>
                            <div class="text-muted small">Saldo warga dikonversi ke Rupiah</div>
                        </div>
                        <span class="fs-5 fw-bold text-success">
                            Rp {{ number_format($circulatingPoints * $pointValue, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-body border-0 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Kelola master data di halaman
                    <a href="{{ route('categories.index') }}">Kategori Sampah</a>.
                </div>
            </div>
        </div>
    </div>
@endsection
