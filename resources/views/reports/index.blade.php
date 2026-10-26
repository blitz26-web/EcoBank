@extends('layouts.app')

@section('title', 'Laporan Setoran')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Laporan Setoran</h1>
            <p class="text-muted mb-0">Rekap setoran selesai dan mutasi poin per periode.</p>
        </div>
        <div class="d-flex gap-2 no-print">
            <a href="{{ route('reports.export', request()->only('dari', 'sampai', 'kategori')) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Unduh Excel (CSV)
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Cetak / PDF
            </button>
        </div>
    </div>

    <div class="card app-card mb-4 no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1" for="dari">Dari Tanggal</label>
                    <input type="date" id="dari" name="dari" value="{{ request('dari', $from->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small text-muted mb-1" for="sampai">Sampai Tanggal</label>
                    <input type="date" id="sampai" name="sampai" value="{{ request('sampai', $to->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-8 col-md-4">
                    <label class="form-label small text-muted mb-1" for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" class="form-select">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryId === (int) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-receipt"></i></span>
                    <div>
                        <div class="text-muted small">Jumlah Transaksi</div>
                        <div class="fs-4 fw-bold">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-info-subtle text-info"><i class="bi bi-scale"></i></span>
                    <div>
                        <div class="text-muted small">Total Berat</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalKg, 2, ',', '.') }} <span class="fs-6 fw-normal">kg</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-star-fill"></i></span>
                    <div>
                        <div class="text-muted small">Poin Terbit</div>
                        <div class="fs-4 fw-bold">{{ number_format($totalPoin, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card app-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-cash-coin"></i></span>
                    <div>
                        <div class="text-muted small">Nilai Setara</div>
                        <div class="fs-4 fw-bold">Rp {{ number_format($totalPoin * $pointValue, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-journal-text me-1 text-success"></i>Mutasi Poin Periode Ini</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div class="fw-semibold">Poin diterbitkan (kredit)</div>
                        <span class="fs-4 fw-bold text-success">+{{ number_format($ledgerKredit, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">Poin ditarik (debit)</div>
                        <span class="fs-4 fw-bold text-danger">-{{ number_format($ledgerDebit, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card app-card h-100">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-info-circle me-1 text-success"></i>Keterangan</h2>
                </div>
                <div class="card-body text-muted small">
                    <p class="mb-2">Laporan hanya memuat setoran berstatus <strong>selesai</strong> karena hanya status itu yang menerbitkan poin.</p>
                    <p class="mb-2">Hasil unduhan berformat CSV dengan pemisah titik koma dan sudah disertai BOM UTF-8 agar langsung rapi saat dibuka di Excel.</p>
                    <p class="mb-0">Untuk PDF, klik <strong>Cetak / PDF</strong> lalu pilih tujuan <em>Save as PDF</em> di dialog cetak peramban.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card app-card">
        <div class="card-header bg-body border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-table me-1 text-success"></i>Rincian Setoran</h2>
            <span class="badge bg-secondary">{{ number_format($jumlahTransaksi, 0, ',', '.') }} transaksi</span>
        </div>
        <div class="card-body p-0">
            @if ($deposits->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Tidak ada setoran selesai pada rentang tanggal ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Kode</th>
                                <th>Tanggal</th>
                                <th>Warga</th>
                                <th>Rincian</th>
                                <th class="text-end">Berat</th>
                                <th class="text-end">Poin</th>
                                <th class="pe-4 text-end">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deposits as $deposit)
                                <tr>
                                    <td class="ps-4 fw-semibold">{{ $deposit->deposit_code }}</td>
                                    <td class="text-muted small">{{ $deposit->created_at?->format('d M Y H:i') }}</td>
                                    <td>{{ $deposit->user?->name }}</td>
                                    <td class="small text-muted">
                                        {{ $deposit->items->map(fn ($item) => $item->wasteCategory->name.' ('.number_format((float) $item->weight_in_kg, 2, ',', '.').' kg)')->implode(', ') }}
                                    </td>
                                    <td class="text-end">{{ number_format((float) $deposit->items->sum('weight_in_kg'), 2, ',', '.') }} kg</td>
                                    <td class="text-end fw-semibold text-success">{{ number_format((int) $deposit->total_points, 0, ',', '.') }}</td>
                                    <td class="pe-4 text-end">Rp {{ number_format((int) $deposit->total_points * $pointValue, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($deposits->count() >= 300)
                    <div class="card-footer bg-body border-0 text-muted small">
                        Menampilkan 300 transaksi terbaru. Gunakan rentang tanggal lebih sempit untuk melihat seluruh data, atau unduh CSV.
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        @media print {
            .navbar, footer, .no-print { display: none !important; }
            main.container { max-width: 100% !important; padding: 0 !important; }
            .app-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
        }
    </style>
@endpush
