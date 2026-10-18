{{--
Partials: Tombol pengalih tema terang/gelap + JavaScript-nya.

Pemakaian:
  @include('partials.theme-toggle')                          -- di navbar (bentuk pill)
  @include('partials.theme-toggle', ['floating' => true])    -- tombol melayang (layout tanpa navbar)

Ikon ditentukan murni lewat CSS (lihat .theme-icon-* pada layout):
mode terang menampilkan bulan (ajakan ke gelap), mode gelap menampilkan matahari.
Tersimpan di localStorage (kunci: kashijau-theme) agar konsisten antar halaman.
--}}
<button type="button" id="themeToggle" aria-label="Ganti tema terang/gelap"
        title="Ganti tema terang/gelap"
        @if (! empty($floating))
            class="btn btn-outline-success bg-body position-fixed bottom-0 end-0 m-3 rounded-circle shadow theme-toggle-floating"
        @else
            class="btn border d-flex align-items-center justify-content-center theme-toggle-navbar"
        @endif>
    <i class="bi bi-moon-stars theme-icon-moon"></i>
    <i class="bi bi-sun theme-icon-sun"></i>
</button>

<script>
    (function () {
        var button = document.getElementById('themeToggle');
        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            var root = document.documentElement;
            var next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';

            root.setAttribute('data-bs-theme', next);
            try {
                window.localStorage.setItem('kashijau-theme', next);
            } catch (e) {
                /* Abaikan bila penyimpanan tidak tersedia; tema tetap berganti untuk sesi ini. */
            }
        });
    })();
</script>
