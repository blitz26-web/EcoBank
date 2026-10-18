{{-- 
Partials: Skrip tema (dark mode) — WAJIB di <head>, SEBELUM CSS Bootstrap.

Cara kerja:
  1. Baca preferensi tersimpan di localStorage (kunci: kashijau-theme).
  2. Bila belum pernah memilih, ikuti preferensi sistem (prefers-color-scheme).
  3. Set atribut data-bs-theme pada <html> SECARA SINKRON sebelum halaman
     dirender — mencegah kedipan (flash of wrong theme) saat pindah halaman.

Bootstrap 5.3 merespons atribut ini secara native (warna kartu, tabel, form,
dropdown, modal, dsb. ikut berganti), sehingga tidak butuh CSS replika per
komponen.
--}}
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
            /* localStorage bisa terblokir (mode privat); diamkan pakai light. */
        }
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
