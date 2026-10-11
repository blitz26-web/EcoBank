@extends('layouts.app')

@section('title', 'Dashboard Warga')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Halo, {{ $user->name }}!</h1>
            <p class="text-muted mb-0">Rangkumlah sampah, tukarkan poinnya.</p>
        </div>
        <span class="badge bg-{{ $user->role->badgeClass() }} fs-6 px-3 py-2">
            <i class="bi bi-person-badge me-1"></i>{{ $user->role->label() }}
        </span>
    </div>

    {{-- ==================== Kartu Saldo ==================== --}}
    <div class="card app-card text-white bg-success mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="text-white-50 small mb-1">Saldo Poin Anda</div>
                    <div class="display-5 fw-bold">{{ number_format($user->balance_points, 0, ',', '.') }} <span class="h5 fw-semibold">poin</span></div>
                    <div class="mt-1 opacity-75">
                        <i class="bi bi-cash-coin me-1"></i>
                        Setara Rp {{ number_format($user->balance_points * $pointValue, 0, ',', '.') }}
                        <span class="small">(1 poin = Rp {{ number_format($pointValue, 0, ',', '.') }})</span>
                    </div>
                </div>
                <i class="bi bi-piggy-bank display-3 opacity-50 d-none d-sm-block"></i>
            </div>
        </div>
    </div>

    {{-- ==================== Aksi Cepat ==================== --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('deposits.create') }}" class="btn btn-success">
            <i class="bi bi-basket3 me-1"></i>Ajukan Setoran Mandiri
        </a>
        <a href="{{ route('withdrawals.create') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-coin me-1"></i>Tarik Poin
        </a>
        <a href="{{ route('deposits.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>Riwayat Setoran
        </a>
        <a href="{{ route('withdrawals.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-cash-stack me-1"></i>Riwayat Penarikan
        </a>
        <a href="{{ route('points.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-journal-text me-1"></i>Riwayat Poin
        </a>
    </div>

    {{-- ==================== Statistik ==================== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-trash3"></i></span>
                    <div>
                        <div class="text-muted small">Total Setoran</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalDeposits, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-star-fill"></i></span>
                    <div>
                        <div class="text-muted small">Poin Diperoleh</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalPoints, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-info-subtle text-info"><i class="bi bi-cash-stack"></i></span>
                    <div>
                        <div class="text-muted small">Penarikan Aktif</div>
                        <div class="fs-4 fw-bold">
                            @if ($hasActiveWithdrawal)
                                <span class="badge bg-info">Sedang diproses</span>
                            @else
                                Tidak ada
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ==================== Riwayat Mutasi Poin ==================== --}}
        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-journal-text me-1 text-success"></i>5 Mutasi Poin Terakhir</h2>
                    <a href="{{ route('points.index') }}" class="small text-decoration-none">Lihat semua <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body p-0">
                    @if ($recentLedgers->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada mutasi poin. Setor sampah Anda untuk mulai mengumpulkan poin!
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Arah</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                        <th class="text-end">Saldo Akhir</th>
                                        <th class="pe-4 text-end">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentLedgers as $ledger)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="badge bg-{{ $ledger->type->badgeClass() }}">
                                                    <i class="bi bi-{{ $ledger->type === \App\Enums\LedgerType::Credit ? 'plus-lg' : 'dash-lg' }} me-1"></i>
                                                    {{ $ledger->type->label() }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold {{ $ledger->type === \App\Enums\LedgerType::Credit ? 'text-success' : 'text-danger' }}">
                                                {{ $ledger->type->sign() > 0 ? '+' : '-' }}{{ number_format($ledger->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="text-muted small">{{ $ledger->description ?? '-' }}</td>
                                            <td class="text-end">{{ number_format($ledger->balance_after, 0, ',', '.') }} poin</td>
                                            <td class="pe-4 text-end text-muted small">{{ $ledger->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
