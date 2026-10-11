@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 fw-bold mb-0"><i class="bi bi-person-gear me-2 text-success"></i>Profil Saya</h1>
                <span class="badge bg-{{ $user->role->badgeClass() }} px-3 py-2">
                    {{ $user->role->label() }}
                </span>
            </div>

            {{-- ==================== Data Profil ==================== --}}
            <div class="card app-card mb-4">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-person me-1 text-success"></i>Data Diri</h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.update') }}" novalidate>
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label fw-semibold">No. Telepon</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                @error('phone')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-sm-end mt-4">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ==================== Ganti Kata Sandi ==================== --}}
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h2 class="h6 fw-bold mb-0"><i class="bi bi-key me-1 text-success"></i>Ganti Kata Sandi</h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.password.update') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="current_password" class="form-label fw-semibold">Kata Sandi Saat Ini</label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                       id="current_password" name="current_password" required
                                       autocomplete="current-password">
                                @error('current_password')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label fw-semibold">Kata Sandi Baru</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required autocomplete="new-password">
                                <div class="form-text">Minimal 8 karakter.</div>
                                @error('password')
                                    <div class="form-text text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Ulangi Kata Sandi Baru</label>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation"
                                       required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-sm-end mt-4">
                            <button type="submit" class="btn btn-outline-success px-4">
                                <i class="bi bi-shield-check me-1"></i>Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
