@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Kelola Pengguna</h1>
            <p class="text-muted mb-0">Buat akun baru, ubah peran, dan aktifkan/nonaktifkan akun.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-success">
            <i class="bi bi-person-plus me-1"></i>Buat Akun
        </a>
    </div>

    <div class="card app-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label small text-muted mb-1" for="q">Cari nama atau email</label>
                    <input type="text" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="mis. Budi atau warga@kashijau.test">
                </div>
                <div class="col-8 col-md-4">
                    <label class="form-label small text-muted mb-1" for="role">Peran</label>
                    <select id="role" name="role" class="form-select">
                        <option value="">Semua peran</option>
                        <option value="warga" {{ $role === 'warga' ? 'selected' : '' }}>Warga</option>
                        <option value="petugas" {{ $role === 'petugas' ? 'selected' : '' }}>Petugas</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-4 col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card app-card">
        <div class="card-header bg-body border-0 pt-3 pb-0">
            <h2 class="h6 fw-bold mb-0"><i class="bi bi-people me-1 text-success"></i>Daftar Pengguna</h2>
        </div>
        <div class="card-body p-0">
            @if ($users->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-people fs-1 d-block mb-2"></i>
                    Tidak ada pengguna yang cocok dengan pencarian.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Pengguna</th>
                                <th>Peran</th>
                                <th class="text-end">Saldo Poin</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $account)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold">{{ $account->name }}</div>
                                        <div class="small text-muted">{{ $account->email }}</div>
                                        <div class="small text-muted">{{ $account->phone ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @if ($account->is(auth()->user()))
                                            <span class="badge bg-{{ $account->role->badgeClass() }}">{{ $account->role->label() }}</span>
                                            <div class="small text-muted mt-1">akun Anda</div>
                                        @else
                                            <form method="POST" action="{{ route('users.role', $account) }}" class="d-flex gap-1" style="max-width: 190px;">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role" class="form-select form-select-sm">
                                                    <option value="warga" {{ $account->role->value === 'warga' ? 'selected' : '' }}>Warga</option>
                                                    <option value="petugas" {{ $account->role->value === 'petugas' ? 'selected' : '' }}>Petugas</option>
                                                    <option value="admin" {{ $account->role->value === 'admin' ? 'selected' : '' }}>Admin</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Simpan peran"><i class="bi bi-check2"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format((int) $account->balance_points, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($account->is_active)
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i>Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @if ($account->is(auth()->user()))
                                            <span class="text-muted small">-</span>
                                        @else
                                            <form method="POST" action="{{ route('users.toggle', $account) }}">
                                                @csrf
                                                @method('PATCH')
                                                @if ($account->is_active)
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-person-slash me-1"></i>Nonaktifkan
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-person-check me-1"></i>Aktifkan
                                                    </button>
                                                @endif
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body border-0">
                    {{ $users->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        Akun yang dinonaktifkan tidak bisa login lagi, tetapi riwayat setoran dan poinnya tetap tersimpan.
    </p>
@endsection
