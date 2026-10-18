<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kas Hijau') | Kas Hijau</title>

    {{-- Set tema (terang/gelap) SEBELUM CSS dimuat agar tidak berkedip --}}
    @include('partials.theme-head')

    {{-- Bootstrap 5.3 + Bootstrap Icons via CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* Latar aplikasi: hijau muda di tema terang, hijau gelap pekat di tema gelap. */
        [data-bs-theme="light"] body {
            background-color: #f4f9f6;
        }
        [data-bs-theme="dark"] body {
            background-color: #0e1713;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        /* Transisi halus saat berganti tema (jangan berlebihan agar tetap ringan). */
        body, .card, .navbar, footer, .modal-content, .dropdown-menu {
            transition: background-color .2s ease-in-out, color .2s ease-in-out, border-color .2s ease-in-out;
        }
        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: .65rem;
            background: #198754;
            color: #fff;
            font-size: 1.1rem;
        }
        .app-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 .25rem 1rem rgba(25, 135, 84, .08);
        }
        [data-bs-theme="dark"] .app-card {
            /* Bayangan lebih dalam + garis tepi tipis agar kartu tetap terdefinisi di latar gelap. */
            box-shadow: 0 .25rem 1rem rgba(0, 0, 0, .45);
            border: 1px solid var(--bs-border-color);
        }
        .stat-icon {
            width: 3rem;
            height: 3rem;
            border-radius: .9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        /* Ikon tombol tema: bulan saat terang, matahari saat gelap. */
        [data-bs-theme="light"] .theme-icon-sun { display: none; }
        [data-bs-theme="dark"] .theme-icon-moon { display: none; }
        /* Label kecil tambahan (sub-teks item dropdown akun). */
        .extra-small {
            font-size: .72rem;
        }
        .theme-toggle-navbar {
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            font-size: 1.05rem;
            border-radius: .65rem;
        }
        .theme-toggle-navbar:hover {
            color: #198754;
            border-color: #198754;
        }
        [data-bs-theme="dark"] .theme-toggle-navbar:hover {
            color: #75dfb4;
        }
        .theme-toggle-floating {
            width: 3rem;
            height: 3rem;
            font-size: 1.15rem;
            z-index: 1030;
        }
    </style>
</head>
<body>
    {{-- ===================== Navbar ===================== --}}
    <nav class="navbar navbar-expand-lg bg-body shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('dashboard') }}">
                <span class="brand-icon"><i class="bi bi-recycle"></i></span>
                <span class="text-success">Kas Hijau</span>
            </a>

            {{-- Klaster kanan: pengalih tema, menu akun, dan tombol menu.
                 DIKELUARKAN dari menu collapse agar SELALU terlihat di bar
                 navbar — tidak perlu membuka menu (baris tiga) lebih dulu,
                 baik di ponsel maupun desktop. --}}
            <div class="d-flex align-items-center gap-2 ms-auto order-lg-3">
                @include('partials.theme-toggle')

                @php
                    $multiAccounts = auth()->check()
                        ? \App\Services\MultiAccountSession::users(request())
                        : collect();
                    $otherAccounts = $multiAccounts
                        ->reject(fn ($account): bool => (int) $account->id === (int) auth()->id())
                        ->values();
                @endphp

                {{-- Menu pengguna + dukungan multi-akun (pola Google/Gmail) --}}
                <div class="dropdown">
                    <button class="btn border d-flex align-items-center gap-2 dropdown-toggle"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 text-success"></i>
                        <span class="d-none d-sm-inline text-truncate" style="max-width: 8rem;">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 17rem;">
                        <li class="px-3 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-check-fill text-success fs-4"></i>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-body-secondary small text-truncate">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                            <div class="mt-1 d-flex gap-1">
                                <span class="badge bg-{{ auth()->user()->role->badgeClass() }}">
                                    {{ auth()->user()->role->label() }}
                                </span>
                                <span class="badge text-bg-light border">Aktif</span>
                            </div>
                        </li>

                        @if ($otherAccounts->isNotEmpty())
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header">Berpindah ke akun</h6></li>
                            @foreach ($otherAccounts as $account)
                                <li>
                                    <form method="POST" action="{{ route('accounts.switch') }}">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $account->id }}">
                                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2">
                                            <i class="bi bi-person-circle text-body-secondary"></i>
                                            <span class="flex-grow-1 overflow-hidden">
                                                <span class="d-block text-truncate">{{ $account->name }}</span>
                                                <span class="d-block text-body-secondary extra-small">{{ $account->role->label() }}</span>
                                            </span>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        @endif

                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('accounts.create') }}">
                                <i class="bi bi-person-plus me-2"></i>Tambah akun lain
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-gear me-2"></i>Profil Saya
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar dari akun ini
                                </button>
                            </form>
                        </li>
                        @if ($multiAccounts->count() > 1)
                            <li>
                                <form method="POST" action="{{ route('accounts.logoutAll') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-door-open me-2"></i>Keluar dari semua akun
                                    </button>
                                </form>
                            </li>
                        @endif
                    </ul>
                </div>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                        data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                        aria-expanded="false" aria-label="Buka menu navigasi">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse order-lg-2 me-lg-auto" id="mainNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    @if (auth()->user()?->isPetugas() || auth()->user()?->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('officer.queue') ? 'active' : '' }}"
                               href="{{ route('officer.queue') }}">
                                <i class="bi bi-clipboard-check me-1"></i>Antrian
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()?->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.overview') ? 'active' : '' }}"
                               href="{{ route('admin.overview') }}">
                                <i class="bi bi-shield-lock me-1"></i>Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                               href="{{ route('categories.index') }}">
                                <i class="bi bi-basket me-1"></i>Kategori
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    {{-- ===================== Konten ===================== --}}
    <main class="container py-4 flex-grow-1">
        @include('partials.flash')

        @yield('content')
    </main>

    <footer class="border-top bg-body py-3 text-center text-muted small">
        &copy; {{ date('Y') }} Kas Hijau &mdash; Ubah sampah menjadi poin.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
