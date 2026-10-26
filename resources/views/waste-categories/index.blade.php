@extends('layouts.app')

@section('title', 'Kategori Sampah')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Kategori Sampah</h1>
            <p class="text-muted mb-0">Master data tarif poin per satuan takaran.</p>
        </div>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-kategori" id="btn-tambah-kategori">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
        </button>
    </div>

    <div class="alert alert-light border d-flex gap-2 small" role="alert">
        <i class="bi bi-info-circle text-success mt-1"></i>
        <div>
            Perubahan tarif poin hanya memengaruhi setoran yang dibuat <strong>setelah</strong> perubahan.
            Poin setoran lama tersimpan permanen pada rinciannya (snapshot historis).
            Kategori yang masih dipakai data setoran tidak dapat dihapus.
        </div>
    </div>

    <div class="card app-card">
        <div class="card-body p-0">
            @if ($categories->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-basket fs-1 d-block mb-2"></i>
                    Belum ada kategori sampah. Tambahkan kategori pertama.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama Kategori</th>
                                <th>Satuan</th>
                                <th>Poin per Satuan</th>
                                <th>Dipakai</th>
                                <th class="pe-4 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td class="ps-4 fw-semibold">
                                        <i class="bi bi-recycle text-success me-1"></i>{{ $category->name }}
                                    </td>
                                    <td>{{ $category->unit }}</td>
                                    <td class="fw-semibold">{{ number_format($category->point_per_unit, 0, ',', '.') }} poin</td>
                                    <td class="small text-muted">{{ number_format($category->deposit_items_count, 0, ',', '.') }} item setoran</td>
                                    <td class="pe-4 text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-outline-primary btn-sm js-edit-kategori"
                                                    data-bs-toggle="modal" data-bs-target="#modal-kategori"
                                                    data-url="{{ route('categories.update', $category) }}"
                                                    data-name="{{ $category->name }}"
                                                    data-unit="{{ $category->unit }}"
                                                    data-point-per-unit="{{ $category->point_per_unit }}">
                                                <i class="bi bi-pencil me-1"></i>Ubah
                                            </button>
                                            @if ($category->deposit_items_count > 0)
                                                <button type="button" class="btn btn-outline-danger btn-sm" disabled
                                                        title="Masih dipakai {{ number_format($category->deposit_items_count, 0, ',', '.') }} item setoran sehingga tidak dapat dihapus.">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                                      onsubmit="return confirm('Hapus kategori &quot;{{ $category->name }}&quot;?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus kategori">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($categories->hasPages())
            <div class="card-footer bg-body border-0 pb-2">{{ $categories->links() }}</div>
        @endif
    </div>

    <div class="modal fade" id="modal-kategori" tabindex="-1" aria-labelledby="modal-kategori-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" id="form-kategori" action="{{ route('categories.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="_method" value="POST" id="kategori-method">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-kategori-label">
                        <i class="bi bi-recycle text-success me-1"></i><span id="kategori-modal-title">Tambah Kategori</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kategori-name" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kategori-name" name="name" maxlength="100"
                               placeholder="Mis. Botol PET" required>
                    </div>
                    <div class="mb-3">
                        <label for="kategori-unit" class="form-label fw-semibold">Satuan Takaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kategori-unit" name="unit" maxlength="20"
                               list="daftar-satuan" placeholder="kg" required>
                        <datalist id="daftar-satuan">
                            <option value="kg"></option>
                            <option value="pcs"></option>
                            <option value="liter"></option>
                        </datalist>
                    </div>
                    <div class="mb-1">
                        <label for="kategori-point" class="form-label fw-semibold">Poin per Satuan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="kategori-point" name="point_per_unit"
                               min="0" max="1000000" step="1" required>
                        <div class="form-text">Contoh: Botol PET 10 poin/kg berarti 1,25 kg = 13 poin (dibulatkan).</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="kategori-submit-label">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('modal-kategori');
            var form = document.getElementById('form-kategori');
            var methodInput = document.getElementById('kategori-method');
            var titleEl = document.getElementById('kategori-modal-title');
            var storeUrl = form.action;

            document.getElementById('btn-tambah-kategori').addEventListener('click', function () {
                form.action = storeUrl;
                methodInput.value = 'POST';
                titleEl.textContent = 'Tambah Kategori';
                form.reset();
            });

            document.querySelectorAll('.js-edit-kategori').forEach(function (button) {
                button.addEventListener('click', function () {
                    form.action = button.getAttribute('data-url') || storeUrl;
                    methodInput.value = 'PUT';
                    titleEl.textContent = 'Ubah Kategori';

                    document.getElementById('kategori-name').value = button.getAttribute('data-name') || '';
                    document.getElementById('kategori-unit').value = button.getAttribute('data-unit') || 'kg';
                    document.getElementById('kategori-point').value = button.getAttribute('data-point-per-unit') || '0';
                });
            });
        });
    </script>
@endpush
