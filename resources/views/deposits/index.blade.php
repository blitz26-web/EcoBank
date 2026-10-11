@extends('layouts.app')

@section('title', 'Riwayat Setoran')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Riwayat Setoran</h1>
            <p class="text-muted mb-0">Semua transaksi setoran sampah{{ $isWarga ? ' milik Anda' : '' }}.</p>
        </div>
        <a href="{{ route('deposits.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>
            {{ $isWarga ? 'Ajukan Setoran Mandiri' : 'Catat Setoran di Loket' }}
        </a>
    </div>

    {{-- ==================== Filter Status ==================== --}}
    <div class="btn-group btn-group-sm mb-3 flex-wrap" role="group" aria-label="Filter status setoran">
        <a href="{{ route('deposits.index') }}" class="btn {{ $statusFilter === null ? 'btn-success' : 'btn-outline-success' }}">
            Semua
        </a>
        @foreach ($statuses as $status)
            <a href="{{ route('deposits.index', ['status' => $status->value]) }}"
               class="btn {{ $statusFilter === $status ? 'btn-success' : 'btn-outline-success' }}">
                {{ $status->label() }}
            </a>
        @endforeach
    </div>

    <div class="card app-card">
        <div class="card-body p-0">
            @if ($deposits->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-trash3 fs-1 d-block mb-2"></i>
                    Belum ada setoran{{ $statusFilter !== null ? ' dengan status "' . $statusFilter->label() . '"' : '' }}.
                    @if ($isWarga)
                        <div class="mt-2">
                            <a href="{{ route('deposits.create') }}">Ajukan setoran pertama Anda</a>.
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
                                <th>Rincian</th>
                                <th>Total Poin</th>
                                <th>Status</th>
                                <th>Petugas</th>
                                <th>Waktu</th>
                                <th class="pe-4 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deposits as $deposit)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $deposit->deposit_code }}</td>
                                    @if (! $isWarga)
                                        <td>
                                            <div class="fw-semibold">{{ $deposit->user?->name ?? '-' }}</div>
                                            <div class="small text-muted">{{ $deposit->user?->email ?? '' }}</div>
                                        </td>
                                    @endif
                                    <td class="small">
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                                                data-bs-toggle="collapse" data-bs-target="#detail-setoran-{{ $deposit->getKey() }}"
                                                aria-expanded="false" aria-controls="detail-setoran-{{ $deposit->getKey() }}">
                                            {{ $deposit->items->count() }} jenis sampah <i class="bi bi-chevron-expand"></i>
                                        </button>
                                    </td>
                                    <td class="fw-semibold">{{ number_format($deposit->total_points, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $deposit->status->badgeClass() }}">
                                            <i class="bi {{ $deposit->status->icon() }} me-1"></i>{{ $deposit->status->label() }}
                                        </span>
                                    </td>
                                    <td class="small">{{ $deposit->officer?->name ?? '-' }}</td>
                                    <td class="text-muted small">{{ $deposit->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex gap-1">
                                            @can('verify', $deposit)
                                                <form method="POST" action="{{ route('deposits.verify', $deposit) }}"
                                                      onsubmit="return confirm('Verifikasi setoran {{ $deposit->deposit_code }} dan kreditkan {{ number_format($deposit->total_points, 0, ',', '.') }} poin ke saldo {{ $deposit->user?->name }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Kreditkan poin ke saldo warga">
                                                        <i class="bi bi-check2-circle me-1"></i>Verifikasi
                                                    </button>
                                                </form>
                                            @endcan
                                            @can('cancel', $deposit)
                                                <form method="POST" action="{{ route('deposits.cancel', $deposit) }}"
                                                      onsubmit="return confirm('Batalkan setoran {{ $deposit->deposit_code }}? Setoran yang dibatalkan tidak dapat dipulihkan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Batalkan pengajuan ini">
                                                        <i class="bi bi-x-circle me-1"></i>Batalkan
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                {{-- Baris detail yang bisa dilipat --}}
                                <tr class="border-0">
                                    <td colspan="{{ $isWarga ? 7 : 8 }}" class="p-0 border-0">
                                        <div class="collapse" id="detail-setoran-{{ $deposit->getKey() }}">
                                            <div class="m-2 mt-0 p-3 bg-light rounded-3 small">
                                                <div class="fw-semibold mb-2">
                                                    <i class="bi bi-list-ul me-1"></i>Rincian Jenis Sampah
                                                </div>
                                                <div class="row g-2">
                                                    @foreach ($deposit->items as $item)
                                                        <div class="col-12 col-md-6">
                                                            <i class="bi bi-dot"></i>
                                                            <span class="fw-semibold">{{ $item->wasteCategory?->name ?? '-' }}</span>
                                                            &mdash; {{ number_format((float) $item->weight_in_kg, 2, ',', '.') }}
                                                            {{ $item->wasteCategory?->unit ?? 'kg' }}
                                                            &rarr; <span class="text-success fw-semibold">{{ number_format($item->points_earned, 0, ',', '.') }} poin</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="text-muted mt-2">
                                                    Kode: {{ $deposit->deposit_code }} &bull;
                                                    Status: {{ $deposit->status->label() }}
                                                    @if ($deposit->officer)
                                                        &bull; Petugas: {{ $deposit->officer->name }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($deposits->hasPages())
            <div class="card-footer bg-body border-0 pb-2">{{ $deposits->links() }}</div>
        @endif
    </div>
@endsection
