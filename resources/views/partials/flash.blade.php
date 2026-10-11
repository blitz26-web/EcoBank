{{-- Pesan status sukses (flash session) --}}
@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <div>
            @if (session('status') === 'profil-updated')
                Profil berhasil diperbarui.
            @elseif (session('status') === 'password-updated')
                Kata sandi berhasil diganti.
            @elseif (session('status') === 'deposit-recorded')
                Setoran berhasil dicatat dan poin langsung masuk ke saldo warga.
            @elseif (session('status') === 'deposit-submitted')
                Pengajuan setoran terkirim. Poin masuk setelah petugas memverifikasi.
            @elseif (session('status') === 'deposit-verified')
                Setoran diverifikasi. Poin telah masuk ke saldo warga.
            @elseif (session('status') === 'deposit-cancelled')
                Setoran berhasil dibatalkan.
            @elseif (session('status') === 'withdrawal-submitted')
                Pengajuan penarikan terkirim. Poin Anda ditahan (hold) sementara.
            @elseif (session('status') === 'withdrawal-processed')
                Penarikan ditandai selesai dibayar.
            @elseif (session('status') === 'withdrawal-rejected')
                Penarikan ditolak dan poin telah dikembalikan ke saldo warga.
            @elseif (session('status') === 'category-created')
                Kategori sampah berhasil ditambahkan.
            @elseif (session('status') === 'category-updated')
                Kategori sampah berhasil diperbarui.
            @elseif (session('status') === 'category-deleted')
                Kategori sampah berhasil dihapus.
            @else
                {{ __(session('status')) }}
            @endif
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

{{-- Pesan error umum (dari catch RuntimeException / QueryException di controller) --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-x-octagon-fill"></i>
        <div>{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

{{-- Ringkasan error validasi --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="fw-semibold mb-1">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>Periksa kembali isian berikut:
        </div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif
