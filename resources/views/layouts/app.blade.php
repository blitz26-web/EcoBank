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
                <span class="text-success">Eco</span>Bank
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar" aria-controls="mainNavbar"
                    aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
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

                {{-- Pengalih tema terang/gelap --}}
                <div class="d-flex align-items-center ms-lg-2 me-lg-2 mt-2 mt-lg-0">
                    @include('partials.theme-toggle')
                </div>

                {{-- Menu pengguna --}}
                <div class="dropdown ms-lg-3">
                    <button class="btn border d-flex align-items-center gap-2 dropdown-toggle"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 text-success"></i>
                        <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="px-3 py-2">
                            <div class="fw-semibold text-truncate" style="max-width: 180px;">{{ auth()->user()->name }}</div>
                            <div class="text-muted small">{{ auth()->user()->email }}</div>
                            <span class="badge bg-{{ auth()->user()->role->badgeClass() }} mt-1">
                                {{ auth()->user()->role->label() }}
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person-gear me-2"></i>Profil Saya
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
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
