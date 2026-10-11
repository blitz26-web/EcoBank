@extends('layouts.app')

@section('title', $isCounterMode ? 'Catat Setoran di Loket' : 'Ajukan Setoran Mandiri')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">
                {{ $isCounterMode ? 'Catat Setoran di Loket' : 'Ajukan Setoran Mandiri' }}
            </h1>
            <p class="text-muted mb-0">Tambahkan satu baris untuk setiap jenis sampah yang disetorkan.</p>
        </div>
        <a href="{{ route('deposits.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Riwayat Setoran
        </a>
    </div>

    @if ($isCounterMode)
        <div class="alert alert-info d-flex gap-2" role="alert">
            <i class="bi bi-info-circle-fill mt-1"></i>
            <div>
                Setoran loket langsung berstatus <strong>Selesai</strong>: poin dihitung di server
                dan masuk ke saldo warga seketika setelah form disimpan.
            </div>
        </div>
    @else
        <div class="alert alert-info d-flex gap-2" role="alert">
            <i class="bi bi-info-circle-fill mt-1"></i>
            <div>
                Pengajuan Anda berstatus <strong>Menunggu Verifikasi</strong>: poin baru masuk ke saldo
                setelah petugas memverifikasi timbangan sampah di loket.
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('deposits.store') }}" id="form-setoran">
        @csrf

        <div class="card app-card mb-4">
            <div class="card-body p-4">
                {{-- Pemilik rekening (khusus petugas/admin) --}}
                @if ($isCounterMode)
                    <div class="mb-4">
                        <label for="user-id" class="form-label fw-semibold">
                            Warga Pemilik Rekening <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('user_id') is-invalid @enderror" id="user-id" name="user_id" required>
                            <option value="">-- Pilih warga --</option>
                            @foreach ($wargaOptions as $warga)
                                <option value="{{ $warga->getKey() }}" @selected(old('user_id') == $warga->getKey())>
                                    {{ $warga->name }} ({{ $warga->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                {{-- Baris item dinamis --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-basket3 me-1 text-success"></i>Jenis Sampah</h2>
                    <span class="text-muted small">Maksimal {{ $maxItems }} jenis per setoran</span>
                </div>

                <div id="item-rows">
                    {{-- Baris diisi oleh JavaScript dari template di bawah --}}
                </div>

                <button type="button" id="btn-add-item" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Jenis Sampah
                </button>
            </div>

            {{-- Ringkasan total --}}
            <div class="card-footer bg-light border-0 rounded-bottom-4 p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="text-muted small">Total Poin (perkiraan)</div>
                        <div class="fs-3 fw-bold text-success" id="total-points">0</div>
                        <div class="text-muted small">
                            &asymp; Rp <span id="total-rupiah">0</span>
                            <span class="ms-1">(1 poin = Rp {{ number_format($pointValue, 0, ',', '.') }})</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg px-4">
                        <i class="bi bi-save me-1"></i>
                        {{ $isCounterMode ? 'Simpan & Kreditkan Poin' : 'Kirim Pengajuan' }}
                    </button>
                </div>
                <div class="form-text mt-2">
                    Poin dihitung ulang di sisi server dari berat &times; tarif kategori; angka di layar
                    hanya perkiraan agar petugas/warga dapat memeriksa input.
                </div>
            </div>
        </div>
    </form>

    {{-- ==================== Template Baris Item ==================== --}}
    <template id="item-row-template">
        <div class="row g-2 align-items-end item-row border-bottom pb-3 mb-3">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold">Jenis Sampah <span class="text-danger">*</span></label>
                <select class="form-select js-category" name="items[__IDX__][waste_category_id]" required>
                    <option value="">-- Pilih kategori --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->getKey() }}" data-point-per-unit="{{ $category->point_per_unit }}">
                            {{ $category->name }} &mdash; {{ number_format($category->point_per_unit, 0, ',', '.') }} poin/{{ $category->unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-5 col-md-3">
                <label class="form-label small fw-semibold">Berat/Jumlah <span class="text-danger">*</span></label>
                <input type="number" class="form-control js-weight" name="items[__IDX__][weight_in_kg]"
                       min="0.01" step="0.01" placeholder="0.00" required>
            </div>
            <div class="col-4 col-md-3">
                <label class="form-label small fw-semibold">Poin</label>
                <div class="form-control-plaintext fw-semibold text-success js-points">0</div>
            </div>
            <div class="col-3 col-md-1 text-md-end">
                <button type="button" class="btn btn-outline-danger btn-sm js-remove" title="Hapus baris ini">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var template = document.getElementById('item-row-template');
            var rowsEl = document.getElementById('item-rows');
            var btnAdd = document.getElementById('btn-add-item');
            var maxItems = {{ (int) $maxItems }};
            var pointValue = {{ (int) $pointValue }};
            var oldItems = @json($oldItems);
            var counter = 0;

            function formatNumber(value) {
                return Number(value).toLocaleString('id-ID');
            }

            function toggleAddButton() {
                btnAdd.disabled = rowsEl.querySelectorAll('.item-row').length >= maxItems;
            }

            function recalc() {
                var total = 0;

                rowsEl.querySelectorAll('.item-row').forEach(function (row) {
                    var select = row.querySelector('.js-category');
                    var weight = parseFloat(row.querySelector('.js-weight').value) || 0;
                    var option = select.options[select.selectedIndex];
                    var ppu = option ? (parseFloat(option.getAttribute('data-point-per-unit')) || 0) : 0;
                    var points = Math.round(weight * ppu);

                    row.querySelector('.js-points').textContent = formatNumber(points);
                    total += points;
                });

                document.getElementById('total-points').textContent = formatNumber(total);
                document.getElementById('total-rupiah').textContent = formatNumber(total * pointValue);
            }

            function addRow(oldItem) {
                if (rowsEl.querySelectorAll('.item-row').length >= maxItems) {
                    return;
                }

                var node = template.content.cloneNode(true);
                var row = node.querySelector('.item-row');
                var placeholders = node.querySelectorAll('[name*="__IDX__"]');

                placeholders.forEach(function (el) {
                    el.name = el.name.replace('__IDX__', String(counter));
                });

                row.querySelector('.js-remove').addEventListener('click', function () {
                    row.remove();
                    toggleAddButton();
                    recalc();
                });

                row.querySelector('.js-category').addEventListener('change', recalc);
                row.querySelector('.js-weight').addEventListener('input', recalc);

                if (oldItem) {
                    row.querySelector('.js-category').value = String(oldItem.waste_category_id || '');
                    row.querySelector('.js-weight').value = String(oldItem.weight_in_kg || '');
                }

                rowsEl.appendChild(node);
                counter += 1;
                toggleAddButton();
                recalc();
            }

            btnAdd.addEventListener('click', function () {
                addRow(null);
            });

            // Pulihkan isian lama setelah error validasi; bila kosong, sediakan satu baris.
            if (oldItems.length > 0) {
                oldItems.forEach(function (item) {
                    addRow(item);
                });
            } else {
                addRow(null);
            }
        });
    </script>
@endpush
