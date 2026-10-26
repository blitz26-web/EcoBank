@extends('layouts.app')

@section('title', 'Antrian Petugas')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Antrian Verifikasi</h1>
            <p class="text-muted mb-0">Ringkasan pekerjaan menunggu tindakan Anda.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('officer.queue') }}" class="btn btn-success">
                <i class="bi bi-clipboard-check me-1"></i>Buka Antrian Kerja
            </a>
            <a href="{{ route('deposits.create') }}" class="btn btn-outline-success">
                <i class="bi bi-plus-lg me-1"></i>Catat Setoran
            </a>
        </div>
        <span class="badge bg-{{ auth()->user()->role->badgeClass() }} fs-6 px-3 py-2">
            <i class="bi bi-person-badge me-1"></i>{{ auth()->user()->role->label() }}
        </span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-hourglass-split"></i></span>
                    <div>
                        <div class="text-muted small">Setoran Pending</div>
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $pendingDeposits->count() }}">{{ number_format($pendingDeposits->count(), 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-info-subtle text-info"><i class="bi bi-cash-stack"></i></span>
                    <div>
                        <div class="text-muted small">Penarikan Menunggu</div>
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $requestedWithdrawals->count() }}">{{ number_format($requestedWithdrawals->count(), 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></span>
                    <div>
                        <div class="text-muted small">Setoran Selesai Hari Ini</div>
                        <div class="fs-4 fw-bold"><span data-count-up="{{ $completedToday }}">{{ number_format($completedToday, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-trash3 me-1 text-success"></i>Setoran Menunggu Verifikasi</h2>
                    <span class="small text-muted">10 terbaru</span>
                </div>
                <div class="card-body p-0">
                    @if ($pendingDeposits->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-check fs-1 d-block mb-2"></i>
                            Tidak ada setoran menunggu verifikasi.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Kode</th>
                                        <th>Warga</th>
                                        <th>Rincian</th>
                                        <th>Total Poin</th>
                                        <th class="pe-4 text-end">Diajukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingDeposits as $deposit)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $deposit->deposit_code }}</td>
                                            <td>{{ $deposit->user?->name ?? '-' }}</td>
                                            <td class="small text-muted">{{ $deposit->items->count() }} jenis sampah</td>
                                            <td>
                                                <span class="badge bg-{{ $deposit->status->badgeClass() }}">
                                                    <i class="bi {{ $deposit->status->icon() }} me-1"></i>{{ $deposit->status->label() }}
                                                </span>
                                                <span class="ms-1 fw-semibold">{{ number_format($deposit->total_points, 0, ',', '.') }} poin</span>
                                            </td>
                                            <td class="pe-4 text-end text-muted small">{{ $deposit->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-body border-0 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Tombol verifikasi tersedia di halaman <a href="{{ route('officer.queue') }}">Antrian Kerja</a>
                    dan <a href="{{ route('deposits.index') }}">Riwayat Setoran</a>.
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-cash-coin me-1 text-success"></i>Penarikan Menunggu Diproses</h2>
                    <span class="small text-muted">10 terbaru</span>
                </div>
                <div class="card-body p-0">
                    @if ($requestedWithdrawals->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Tidak ada penarikan menunggu.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Kode</th>
                                        <th>Warga</th>
                                        <th>Poin</th>
                                        <th>Jumlah Uang</th>
                                        <th>Metode</th>
                                        <th class="pe-4 text-end">Diajukan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestedWithdrawals as $withdrawal)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $withdrawal->withdrawal_code }}</td>
                                            <td>{{ $withdrawal->user?->name ?? '-' }}</td>
                                            <td>{{ number_format($withdrawal->points_amount, 0, ',', '.') }}</td>
                                            <td class="fw-semibold">Rp {{ number_format((float) $withdrawal->cash_amount, 0, ',', '.') }}</td>
                                            <td class="small">
                                                {{ $withdrawal->payment_method }}
                                                @if ($withdrawal->account_number)
                                                    <div class="text-muted">{{ $withdrawal->account_number }}</div>
                                                @endif
                                            </td>
                                            <td class="pe-4 text-end text-muted small">{{ $withdrawal->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-body border-0 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Tombol proses & tolak (refund poin otomatis) tersedia di
                    <a href="{{ route('officer.queue') }}">Antrian Kerja</a>
                    dan <a href="{{ route('withdrawals.index') }}">Riwayat Penarikan</a>.
                </div>
            </div>
        </div>
    </div>
@endsection
