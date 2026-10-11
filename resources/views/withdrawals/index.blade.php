@extends('layouts.app')

@section('title', 'Riwayat Penarikan')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Riwayat Penarikan</h1>
            <p class="text-muted mb-0">Semua pengajuan penarikan poin{{ $isWarga ? ' milik Anda' : '' }}.</p>
        </div>
        @if ($isWarga)
            <a href="{{ route('withdrawals.create') }}" class="btn btn-success">
                <i class="bi bi-cash-coin me-1"></i>Ajukan Penarikan
            </a>
        @endif
    </div>

    {{-- ==================== Filter Status ==================== --}}
    <div class="btn-group btn-group-sm mb-3 flex-wrap" role="group" aria-label="Filter status penarikan">
        <a href="{{ route('withdrawals.index') }}" class="btn {{ $statusFilter === null ? 'btn-success' : 'btn-outline-success' }}">
            Semua
        </a>
        @foreach ($statuses as $status)
            <a href="{{ route('withdrawals.index', ['status' => $status->value]) }}"
               class="btn {{ $statusFilter === $status ? 'btn-success' : 'btn-outline-success' }}">
                {{ $status->label() }}
            </a>
        @endforeach
    </div>

    <div class="card app-card">
        <div class="card-body p-0">
            @if ($withdrawals->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-cash-stack fs-1 d-block mb-2"></i>
                    Belum ada penarikan{{ $statusFilter !== null ? ' dengan status "' . $statusFilter->label() . '"' : '' }}.
                    @if ($isWarga)
                        <div class="mt-2">
                            <a href="{{ route('withdrawals.create') }}">Ajukan penarikan pertama Anda</a>.
                        </div>
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Kode</th>
                                @if (! $isWarga)
                                    <th>Warga</th>
                                @endif
                                <th>Poin</th>
                                <th>Jumlah Uang</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Petugas</th>
                                <th>Waktu</th>
                                <th class="pe-4 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($withdrawals as $withdrawal)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $withdrawal->withdrawal_code }}</td>
                                    @if (! $isWarga)
                                        <td>
                                            <div class="fw-semibold">{{ $withdrawal->user?->name ?? '-' }}</div>
                                            <div class="small text-muted">{{ $withdrawal->user?->email ?? '' }}</div>
                                        </td>
                                    @endif
                                    <td>{{ number_format($withdrawal->points_amount, 0, ',', '.') }}</td>
                                    <td class="fw-semibold">Rp {{ number_format((float) $withdrawal->cash_amount, 0, ',', '.') }}</td>
                                    <td class="small">
                                        {{ $withdrawal->paymentLabel() }}
                                        @if ($withdrawal->account_number)
                                            <div class="text-muted">{{ $withdrawal->account_number }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $withdrawal->status->badgeClass() }}">
                                            <i class="bi {{ $withdrawal->status->icon() }} me-1"></i>{{ $withdrawal->status->label() }}
                                        </span>
                                        @if ($withdrawal->isRejected() && $withdrawal->rejected_reason)
                                            <div class="text-muted small mt-1" style="max-width: 220px;">
                                                <i class="bi bi-chat-left-text me-1"></i>{{ $withdrawal->rejected_reason }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="small">{{ $withdrawal->officer?->name ?? '-' }}</td>
                                    <td class="text-muted small">
                                        {{ $withdrawal->created_at?->format('d M Y H:i') ?? '-' }}
                                        @if ($withdrawal->processed_at)
                                            <div>Diproses: {{ $withdrawal->processed_at->format('d M Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex gap-1">
                                            @can('process', $withdrawal)
                                                <form method="POST" action="{{ route('withdrawals.process', $withdrawal) }}"
                                                      onsubmit="return confirm('Tandai penarikan {{ $withdrawal->withdrawal_code }} selesai dibayar sebesar Rp {{ number_format((float) $withdrawal->cash_amount, 0, ',', '.') }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-cash-coin me-1"></i>Proses
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('reject', $withdrawal)
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#modal-tolak-penarikan"
                                                        data-action="{{ route('withdrawals.reject', $withdrawal) }}"
                                                        data-code="{{ $withdrawal->withdrawal_code }}"
                                                        data-info="{{ number_format($withdrawal->points_amount, 0, ',', '.') }} poin &bull; Rp {{ number_format((float) $withdrawal->cash_amount, 0, ',', '.') }}">
                                                    <i class="bi bi-x-circle me-1"></i>Tolak
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($withdrawals->hasPages())
            <div class="card-footer bg-body border-0 pb-2">{{ $withdrawals->links() }}</div>
        @endif
    </div>

    @include('partials.modal-reject-withdrawal')
@endsection
