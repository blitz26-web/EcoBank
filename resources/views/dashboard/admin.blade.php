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

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-people"></i></span>
                    <div>
                        <div class="text-muted small">Warga</div>
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $wargaCount }}">{{ number_format($wargaCount, 0, ',', '.') }}</span></div>
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
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $petugasCount }}">{{ number_format($petugasCount, 0, ',', '.') }}</span></div>
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
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $adminCount }}">{{ number_format($adminCount, 0, ',', '.') }}</span></div>
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
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $circulatingPoints }}">{{ number_format($circulatingPoints, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-graph-up-arrow me-1 text-success"></i>Tren Poin 30 Hari Terakhir</h2>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" style="max-height: 260px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-pie-chart me-1 text-success"></i>Komposisi Kategori</h2>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" style="max-height: 260px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-trophy me-1 text-success"></i>Warga Teraktif</h2>
                    <span class="text-muted small">total poin sepanjang waktu</span>
                </div>
                <div class="card-body p-0">
                    @if ($leaderboard->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-trophy fs-1 d-block mb-2"></i>
                            Belum ada warga yang mengumpulkan poin.
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($leaderboard as $index => $row)
                                <div class="list-group-item d-flex align-items-center gap-3 px-4">
                                    <span class="badge {{ ['bg-warning text-dark', 'bg-secondary', 'bg-danger'][$index] ?? 'bg-light text-dark' }} rounded-pill px-3">{{ $index + 1 }}</span>
                                    <div class="fw-semibold flex-grow-1 text-truncate">{{ $row->name }}</div>
                                    <span class="fw-bold text-success">{{ number_format((int) $row->total_earned, 0, ',', '.') }} poin</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-lightning me-1 text-success"></i>Akses Cepat</h2>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-success text-start">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan Setoran &amp; Mutasi Poin
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-success text-start">
                        <i class="bi bi-people me-2"></i>Kelola Pengguna
                    </a>
                    <a href="{{ route('admin.overview') }}" class="btn btn-outline-success text-start">
                        <i class="bi bi-shield-lock me-2"></i>Panel Admin Lengkap
                    </a>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-basket me-2"></i>Kategori Sampah
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
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

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            (function () {
                var textColor = getComputedStyle(document.body).color;
                var gridColor = 'rgba(128, 128, 128, 0.15)';

                new Chart(document.getElementById('trendChart'), {
                    type: 'line',
                    data: {
                        labels: @json($trendLabels),
                        datasets: [{
                            data: @json($trendData),
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.12)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 2
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: textColor, maxTicksLimit: 10 }, grid: { color: gridColor } },
                            y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } }
                        }
                    }
                });

                new Chart(document.getElementById('categoryChart'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($categoryLabels),
                        datasets: [{
                            data: @json($categoryData),
                            backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997', '#6610f2', '#d63384']
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { color: textColor, boxWidth: 14 } } }
                    }
                });
            })();
        </script>
    @endpush
@endsection
