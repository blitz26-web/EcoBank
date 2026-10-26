<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Kas Hijau') | Kas Hijau</title>

    @include('partials.theme-head')

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        [data-bs-theme="light"] body {
            background: linear-gradient(135deg, #e8f5ee 0%, #f4fbf7 55%, #eaf6ef 100%);
        }
        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #10201a 0%, #0b1411 55%, #0e1a15 100%);
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .guest-card {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: 0 .75rem 2rem rgba(25, 135, 84, .12);
        }
        [data-bs-theme="dark"] .guest-card {
            box-shadow: 0 .75rem 2rem rgba(0, 0, 0, .5);
            border: 1px solid var(--bs-border-color);
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
    <main class="container d-flex flex-column justify-content-center align-items-center py-5 flex-grow-1">
        <div class="w-100" style="max-width: 460px;">
            <div class="text-center mb-4">
                <span class="brand-icon mb-2"><i class="bi bi-recycle"></i></span>
                <h1 class="h3 fw-bold text-success mb-0">Kas Hijau</h1>
                <p class="text-muted small mb-0">Bank Sampah Digital</p>
            </div>

            @include('partials.flash')

            @yield('content')
        </div>
    </main>

    @include('partials.theme-toggle', ['floating' => true])

    <footer class="text-center text-muted small pb-4">
        &copy; {{ date('Y') }} Kas Hijau &mdash; Ubah sampah menjadi poin.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @include('partials.motion')
</body>
</html>
