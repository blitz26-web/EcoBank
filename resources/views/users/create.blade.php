@extends('layouts.app')

@section('title', 'Buat Akun Pengguna')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card app-card">
                <div class="card-header bg-body border-0 pt-3 pb-0">
                    <h1 class="h6 fw-bold mb-0"><i class="bi bi-person-plus me-1 text-success"></i>Buat Akun Pengguna</h1>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label small text-muted mb-1" for="name">Nama Lengkap</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1" for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1" for="phone">Nomor HP</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted mb-1" for="role">Peran</label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="warga" {{ old('role') === 'warga' ? 'selected' : '' }}>Warga</option>
                                <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                            </select>
                            <div class="form-text">Akun admin dibuat langsung lewat seeder, bukan dari halaman ini.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1" for="password">Kata Sandi</label>
                            <input type="password" id="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1" for="password_confirmation">Ulangi Kata Sandi</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check2 me-1"></i>Simpan Akun
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
