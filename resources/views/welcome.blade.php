<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kas Hijau | Ubah Sampah Menjadi Poin</title>

    {{-- Set tema (terang/gelap) SEBELUM CSS dimuat agar tidak berkedip --}}
    @include('partials.theme-head')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        [data-bs-theme="light"] body {
            background: linear-gradient(160deg, #e8f5ee 0%, #ffffff 45%, #eaf6ef 100%);
        }
        [data-bs-theme="dark"] body {
            background: linear-gradient(160deg, #10201a 0%, #0b1411 45%, #0e1a15 100%);
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            background: #198754;
            color: #fff;
            font-size: 1.75rem;
        }
        .feature-icon {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .hero-card { border: 0; border-radius: 1.5rem; box-shadow: 0 .75rem 2rem rgba(25, 135, 84, .12); }
        [data-bs-theme="dark"] .hero-card {
            box-shadow: 0 .75rem 2rem rgba(0, 0, 0, .5);
            border: 1px solid var(--bs-border-color);
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
    </style>
</head>
<body>
    <nav class="navbar bg-body shadow-sm">
        <div class="container">
            <span class="navbar-brand d-flex align-items-center gap-2 fw-bold">
                <span class="brand-icon" style="width: 2.25rem; height: 2.25rem; border-radius: .65rem; font-size: 1.1rem;">
                    <i class="bi bi-recycle"></i>
                </span>
                <span class="text-success">Kas Hijau</span>
            </span>
            <div class="d-flex align-items-center gap-2">
                @include('partials.theme-toggle')
                <a href="{{ route('login') }}" class="btn btn-outline-success">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-success">Daftar</a>
            </div>
        </div>
    </nav>

    <main class="container flex-grow-1 py-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold mb-3">Ubah Sampah Menjadi <span class="text-success">Poin</span></h1>
            <p class="lead text-muted mb-4">
                Kas Hijau mencatat setoran sampah, menghitung poin otomatis, dan mencairkannya
                menjadi uang tunai &mdash; semuanya transparan dan teraudit.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-success btn-lg px-4">
                    <i class="bi bi-person-plus me-1"></i>Mulai Sekarang
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4">
                    Sudah punya akun?
                </a>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-12 col-md-4">
                <div class="card hero-card h-100">
                    <div class="card-body p-4 text-center">
                        <span class="feature-icon bg-success-subtle text-success mb-3"><i class="bi bi-trash3"></i></span>
                        <h2 class="h5 fw-bold">Setor Sampah</h2>
                        <p class="text-muted small mb-0">
                            Timbang sampah terpilah di loket. Poin dihitung otomatis:
                            berat (kg) dikali nilai poin per kategori.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card hero-card h-100">
                    <div class="card-body p-4 text-center">
                        <span class="feature-icon bg-warning-subtle text-warning mb-3"><i class="bi bi-star-fill"></i></span>
                        <h2 class="h5 fw-bold">Kumpulkan Poin</h2>
                        <p class="text-muted small mb-0">
                            Setiap perubahan saldo tercatat di buku besar (ledger) yang
                            tidak bisa diubah &mdash; riwayat Anda selalu bisa dipercaya.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card hero-card h-100">
                    <div class="card-body p-4 text-center">
                        <span class="feature-icon bg-info-subtle text-info mb-3"><i class="bi bi-cash-coin"></i></span>
                        <h2 class="h5 fw-bold">Tarik Jadi Uang</h2>
                        <p class="text-muted small mb-0">
                            Tukar poin menjadi Rupiah via transfer bank, e-wallet, atau tunai
                            di loket. Rate resmi 1 poin = Rp 100.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center text-muted small pb-4">
        &copy; {{ date('Y') }} Kas Hijau &mdash; Bank sampah digital untuk lingkungan lebih bersih.
    </footer>
</body>
</html>
