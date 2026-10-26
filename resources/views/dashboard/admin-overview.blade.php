@extends('layouts.app')

@section('title', 'Panel Admin')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Panel Admin</h1>
            <p class="text-muted mb-0">
                Kendali penuh sistem: rekonsiliasi ledger, transaksi terbaru, dan antrian kerja.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('officer.queue') }}" class="btn btn-success">
                <i class="bi bi-clipboard-check me-1"></i>Buka Antrian
            </a>
            <span class="badge bg-{{ auth()->user()->role->badgeClass() }} fs-6 px-3 py-2">
                <i class="bi bi-person-badge me-1"></i>{{ auth()->user()->role->label() }}
            </span>
        </div>
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
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-journal-check me-1 text-success"></i>Rekonsiliasi Ledger
                    </h2>
                    @if ($reconciliationOk)
                        <span class="badge bg-success-subtle text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>Konsisten
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Selisih terdeteksi
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($reconciliationOk)
                        <div class="d-flex align-items-center gap-3">
                            <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-patch-check"></i></span>
                            <div>
                                <div class="fw-semibold">Saldo = rekap point_ledgers</div>
                                <div class="text-muted small">
                                    Invarian buku besar terpenuhi untuk seluruh pengguna.
                                    Setiap mutasi poin tercatat utuh dan dapat diaudit.
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted small mb-2">
                            Pengguna berikut memiliki saldo yang TIDAK cocok dengan rekap ledger &mdash;
                            segera audit mutasinya:
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th class="text-end">Saldo Kolom</th>
                                        <th class="text-end">Rekap Ledger</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ledgerMismatches as $row)
                                        <tr>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($row->balance_points, 0, ',', '.') }}</td>
                                            <td class="text-end fw-semibold text-danger">{{ number_format((int) $row->ledger_total, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-body border-0 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Invarian dijaga oleh <code>PointLedgerService</code>; entri ledger bersifat imutabel.
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-trash3 me-1 text-success"></i>Setoran Terbaru</h2>
                    <a href="{{ route('deposits.index') }}" class="small text-decoration-none">Lihat semua</a>
                </div>
                <div class="card-body p-0">
                    @if ($recentDeposits->isEmpty())
                        <p class="text-muted small text-center py-4 mb-0">Belum ada setoran.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Kode</th>
                                        <th>Warga</th>
                                        <th class="text-end">Poin</th>
                                        <th class="text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentDeposits as $deposit)
                                        <tr>
                                            <td class="fw-semibold">{{ $deposit->deposit_code }}</td>
                                            <td>{{ $deposit->user?->name ?? '-' }}</td>
                                            <td class="text-end fw-semibold text-success">+{{ number_format($deposit->total_points, 0, ',', '.') }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-{{ $deposit->status->badgeClass() }}-subtle text-{{ $deposit->status->badgeClass() }}">
                                                    <i class="bi {{ $deposit->status->icon() }} me-1"></i>{{ $deposit->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-cash-coin me-1 text-success"></i>Penarikan Terbaru</h2>
                    <a href="{{ route('withdrawals.index') }}" class="small text-decoration-none">Lihat semua</a>
                </div>
                <div class="card-body p-0">
                    @if ($recentWithdrawals->isEmpty())
                        <p class="text-muted small text-center py-4 mb-0">Belum ada penarikan.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Kode</th>
                                        <th>Warga</th>
                                        <th class="text-end">Nilai</th>
                                        <th class="text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentWithdrawals as $withdrawal)
                                        <tr>
                                            <td class="fw-semibold">{{ $withdrawal->withdrawal_code }}</td>
                                            <td>{{ $withdrawal->user?->name ?? '-' }}</td>
                                            <td class="text-end">
                                                <div class="fw-semibold text-primary">-{{ number_format($withdrawal->points_amount, 0, ',', '.') }} poin</div>
                                                <div class="text-muted small">Rp {{ number_format($withdrawal->cash_amount, 0, ',', '.') }}</div>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-{{ $withdrawal->status->badgeClass() }}-subtle text-{{ $withdrawal->status->badgeClass() }}">
                                                    <i class="bi {{ $withdrawal->status->icon() }} me-1"></i>{{ $withdrawal->status->label() }}
                                                </span>
                                            </td>
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

    <div class="row g-4">
        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-bar-chart-line me-1 text-success"></i>Saldo Tertinggi (5 Warga)</h2>
                </div>
                <div class="card-body">
                    @forelse ($topWarga as $warga)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold">{{ $warga->name }}</span>
                                <span class="text-muted small">
                                    {{ number_format($warga->balance_points, 0, ',', '.') }} poin
                                </span>
                            </div>
                            <div class="progress" role="progressbar"
                                 aria-label="Saldo {{ $warga->name }}"
                                 aria-valuenow="{{ $warga->balance_points }}" aria-valuemin="0"
                                 aria-valuemax="{{ max($maxWargaBalance, 1) }}"
                                 style="height: .5rem;">
                                <div class="progress-bar bg-success"
                                     style="width: {{ max(4, (int) round($warga->balance_points / max($maxWargaBalance, 1) * 100)) }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small text-center py-4 mb-0">Belum ada warga terdaftar.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-list-task me-1 text-success"></i>Antrian Kerja Petugas</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <div class="fw-semibold">Setoran menunggu verifikasi</div>
                            <div class="text-muted small">Diajukan warga, belum dicek petugas</div>
                        </div>
                        <span class="badge bg-warning-subtle text-warning fs-5 px-3 py-2">
                            {{ number_format($pendingDepositCount, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <div class="fw-semibold">Penarikan menunggu diproses</div>
                            <div class="text-muted small">Poin sudah di-hold, menunggu pembayaran</div>
                        </div>
                        <span class="badge bg-info-subtle text-info fs-5 px-3 py-2">
                            {{ number_format($requestedWithdrawalCount, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('officer.queue') }}" class="btn btn-sm btn-success">
                            <i class="bi bi-clipboard-check me-1"></i>Buka Antrian
                        </a>
                        <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-basket me-1"></i>Kelola Kategori
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
