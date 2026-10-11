<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Terjadi Kesalahan') | EcoBank</title>

    {{-- Set tema (terang/gelap) SEBELUM CSS dimuat agar tidak berkedip --}}
    @include('partials.theme-head')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        [data-bs-theme="light"] body {
            background: linear-gradient(135deg, #f8f9fa 0%, #eef5f1 100%);
        }
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #131b17 0%, #0b1411 100%);
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
            width: 3rem;
            height: 3rem;
            border-radius: .9rem;
            background: #198754;
            color: #fff;
            font-size: 1.5rem;
        }
        .error-code {
            font-size: clamp(4rem, 18vw, 7rem);
            font-weight: 800;
            line-height: 1;
            color: #198754;
            opacity: .85;
        }
        [data-bs-theme="dark"] .error-code {
            color: #75dfb4;
        }
        /* Ikon tombol tema: bulan saat terang, matahari saat gelap. */
        [data-bs-theme="light"] .theme-icon-sun { display: none; }
        [data-bs-theme="dark"] .theme-icon-moon { display: none; }
        .theme-toggle-floating {
            width: 3rem;
            height: 3rem;
            font-size: 1.15rem;
            z-index: 1030;
        }
    </style>
</head>
<body>
    <main class="container d-flex flex-column justify-content-center align-items-center text-center py-5 flex-grow-1">
        <span class="brand-icon mb-3"><i class="bi bi-recycle"></i></span>
        @yield('content')
    </main>

    {{-- Pengalih tema (tanpa navbar) --}}
    @include('partials.theme-toggle', ['floating' => true])

    <footer class="text-center text-muted small pb-4">
        &copy; {{ date('Y') }} EcoBank &mdash; Bank sampah digital.
    </footer>
</body>
</html>
