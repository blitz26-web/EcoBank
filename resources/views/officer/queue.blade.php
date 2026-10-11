@extends('layouts.app')

@section('title', 'Antrian Petugas')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Antrian Kerja</h1>
            <p class="text-muted mb-0">Verifikasi setoran & tindak lanjut penarikan poin.</p>
        </div>
        <a href="{{ route('deposits.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Catat Setoran di Loket
        </a>
    </div>

    <div class="row g-4">
        {{-- ==================== Setoran Pending ==================== --}}
        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-trash3 me-1 text-success"></i>Setoran Menunggu Verifikasi
                        <span class="badge bg-warning text-dark ms-1">{{ $pendingDeposits->total() }}</span>
                    </h2>
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
                                        <th>Diajukan</th>
                                        <th class="pe-4 text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingDeposits as $deposit)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $deposit->deposit_code }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $deposit->user?->name ?? '-' }}</div>
                                                <div class="small text-muted">{{ $deposit->user?->email ?? '' }}</div>
                                            </td>
                                            <td class="small">
                                                @foreach ($deposit->items as $item)
                                                    <div class="text-muted">
                                                        {{ $item->wasteCategory?->name ?? '-' }}:
                                                        <span class="fw-semibold text-body">{{ number_format((float) $item->weight_in_kg, 2, ',', '.') }}</span>
                                                        {{ $item->wasteCategory?->unit ?? 'kg' }}
                                                        &rarr; {{ number_format($item->points_earned, 0, ',', '.') }} poin
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="fw-semibold">{{ number_format($deposit->total_points, 0, ',', '.') }}</td>
                                            <td class="text-muted small">{{ $deposit->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="pe-4 text-end">
                                                <form method="POST" action="{{ route('deposits.verify', $deposit) }}"
                                                      onsubmit="return confirm('Verifikasi setoran {{ $deposit->deposit_code }} dan kreditkan {{ number_format($deposit->total_points, 0, ',', '.') }} poin ke saldo {{ $deposit->user?->name }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-check2-circle me-1"></i>Verifikasi
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if ($pendingDeposits->hasPages())
                    <div class="card-footer bg-body border-0 pb-2">{{ $pendingDeposits->links() }}</div>
                @endif
            </div>
        </div>

        {{-- ==================== Penarikan Requested ==================== --}}
        <div class="col-12">
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-cash-coin me-1 text-success"></i>Penarikan Menunggu Diproses
                        <span class="badge bg-info text-dark ms-1">{{ $requestedWithdrawals->total() }}</span>
                    </h2>
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
                                        <th>Diajukan</th>
                                        <th class="pe-4 text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestedWithdrawals as $withdrawal)
                                        <tr>
                                            <td class="ps-4 fw-semibold">{{ $withdrawal->withdrawal_code }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $withdrawal->user?->name ?? '-' }}</div>
                                                <div class="small text-muted">{{ $withdrawal->user?->email ?? '' }}</div>
                                            </td>
                                            <td>{{ number_format($withdrawal->points_amount, 0, ',', '.') }}</td>
                                            <td class="fw-semibold">Rp {{ number_format((float) $withdrawal->cash_amount, 0, ',', '.') }}</td>
                                            <td class="small">
                                                {{ $withdrawal->paymentLabel() }}
                                                @if ($withdrawal->account_number)
                                                    <div class="text-muted">{{ $withdrawal->account_number }}</div>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $withdrawal->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="pe-4 text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <form method="POST" action="{{ route('withdrawals.process', $withdrawal) }}"
                                                          onsubmit="return confirm('Tandai penarikan {{ $withdrawal->withdrawal_code }} selesai dibayar sebesar Rp {{ number_format((float) $withdrawal->cash_amount, 0, ',', '.') }}?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="bi bi-cash-coin me-1"></i>Proses
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#modal-tolak-penarikan"
                                                            data-action="{{ route('withdrawals.reject', $withdrawal) }}"
                                                            data-code="{{ $withdrawal->withdrawal_code }}"
                                                            data-info="{{ number_format($withdrawal->points_amount, 0, ',', '.') }} poin &bull; Rp {{ number_format((float) $withdrawal->cash_amount, 0, ',', '.') }}">
                                                        <i class="bi bi-x-circle me-1"></i>Tolak
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if ($requestedWithdrawals->hasPages())
                    <div class="card-footer bg-body border-0 pb-2">{{ $requestedWithdrawals->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal tolak penarikan (partial dipakai bersama halaman riwayat penarikan) --}}
    @include('partials.modal-reject-withdrawal')
@endsection
