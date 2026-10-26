<script>
    (function () {
        var theme = 'light';
        try {
            var stored = window.localStorage.getItem('kashijau-theme');
            if (stored === 'dark' || stored === 'light') {
                theme = stored;
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                theme = 'dark';
            }
        } catch (e) {
        }
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
