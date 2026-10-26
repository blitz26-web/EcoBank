@extends('layouts.app')

@section('title', 'Riwayat Mutasi Poin')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Riwayat Mutasi Poin</h1>
            <p class="text-muted mb-0">Audit trail pribadi: setiap perubahan saldo tercatat di sini.</p>
        </div>
        <span class="badge bg-success-subtle text-success fs-6 px-3 py-2">
            <i class="bi bi-journal-check me-1"></i>Ledger Imutabel
        </span>
    </div>

    <div class="btn-group btn-group-sm mb-3 flex-wrap" role="group" aria-label="Filter arah mutasi">
        <a href="{{ route('points.index') }}" class="btn {{ $typeFilter === null ? 'btn-success' : 'btn-outline-success' }}">
            Semua
        </a>
        @foreach ($types as $type)
            <a href="{{ route('points.index', ['type' => $type->value]) }}"
               class="btn {{ $typeFilter === $type ? 'btn-success' : 'btn-outline-success' }}">
                {{ $type->label() }}
            </a>
        @endforeach
    </div>

    <div class="card app-card">
        <div class="card-body p-0">
            @if ($ledgers->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                    Belum ada mutasi poin{{ $typeFilter !== null ? ' dengan arah "' . $typeFilter->label() . '"' : '' }}.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Arah</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                                <th>Saldo Akhir</th>
                                <th class="pe-4 text-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledgers as $ledger)
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
                                    <td class="small">
                                        {{ $ledger->description ?? '-' }}
                                        @if ($ledger->reference_type)
                                            <div class="text-muted">
                                                <i class="bi bi-link-45deg"></i>{{ class_basename($ledger->reference_type) }} #{{ $ledger->reference_id }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ number_format($ledger->balance_after, 0, ',', '.') }} poin</td>
                                    <td class="pe-4 text-end text-muted small">{{ $ledger->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($ledgers->hasPages())
            <div class="card-footer bg-body border-0 pb-2">{{ $ledgers->links() }}</div>
        @endif
    </div>

    <div class="text-muted small mt-3">
        <i class="bi bi-shield-check me-1"></i>
        Entri pada halaman ini bersifat permanen (tidak dapat diubah/dihapus) sehingga saldo Anda
        selalu dapat direkonsiliasi dari rekap mutasi.
    </div>
@endsection
