@extends('layouts.app')

@section('title', 'Tambah Akun')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7 col-xl-6">
            <div class="card app-card">
                <div class="card-body p-4 p-sm-5">
                    <h1 class="h4 fw-bold mb-1">Tambah akun lain</h1>
                    <p class="text-body-secondary small mb-4">
                        Masuk ke akun Kas Hijau lain <em>tanpa keluar</em> dari akun yang sedang aktif.
                        Setelah ditambahkan, Anda dapat berpindah antar akun kapan saja
                        lewat menu akun di navbar — tanpa mengisi kredensial lagi.
                    </p>

                    <div class="alert alert-info d-flex align-items-center gap-2 py-2 small mb-4">
                        <i class="bi bi-person-check-fill"></i>
                        <div>
                            Akun aktif saat ini:
                            <strong>{{ $activeAccount->name }}</strong>
                            <span class="text-body-secondary">({{ $activeAccount->email }})</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('accounts.store') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email akun lain</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}"
                                       placeholder="nama@contoh.com" required autofocus autocomplete="username">
                            </div>
                            @error('email')
                                <div class="form-text text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Kata Sandi akun tersebut</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required autocomplete="current-password">
                            </div>
                            @error('password')
                                <div class="form-text text-danger">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Kredensial hanya dipakai sekali untuk memverifikasi akun tujuan.
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-person-plus me-1"></i>Masuk &amp; Tambahkan Akun
                            </button>
                        </div>
                    </form>

                    @if ($accounts->count() > 0)
                        <hr class="my-4">
                        <h2 class="h6 text-body-secondary fw-semibold mb-2">Akun terdaftar pada sesi ini</h2>
                        <ul class="list-group list-group-flush">
                            @foreach ($accounts as $account)
                                <li class="list-group-item bg-transparent px-0 d-flex justify-content-between align-items-center gap-2">
                                    <span class="text-truncate">
                                        <i class="bi bi-person-circle text-body-secondary me-1"></i>{{ $account->name }}
                                        <span class="text-body-secondary extra-small d-block d-sm-inline">
                                            &middot; {{ $account->role->label() }}
                                        </span>
                                    </span>
                                    @if ((int) $account->id === (int) $activeAccount->id)
                                        <span class="badge bg-success-subtle text-success-emphasis border">Aktif</span>
                                    @else
                                        <span class="badge text-bg-light border">Siap dipakai</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="card-footer bg-body border-0 text-center py-3">
                    <a href="{{ route('dashboard') }}" class="small fw-semibold text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
