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
            }
        });
    })();
</script>
