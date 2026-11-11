{{--
    Mode malam. Taruh di <head> SEBELUM CSS lain dimuat, agar tema terpasang
    sebelum halaman tergambar (tanpa kedipan putih).
    Pilihan disimpan di browser (localStorage 'sp-theme'); jika belum pernah memilih,
    mengikuti pengaturan sistem (Windows/Android).
--}}
<script>
    (function () {
        var t = null;
        try { t = localStorage.getItem('sp-theme'); } catch (e) {}
        if (t !== 'dark' && t !== 'light') {
            t = window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-theme', t);

        // Dipanggil tombol bulan/matahari
        window.spToggleTheme = function () {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            var root = document.documentElement;
            root.classList.add('sp-theme-anim');
            root.setAttribute('data-theme', next);
            if (document.body) document.body.classList.toggle('dark-mode', next === 'dark');
            try { localStorage.setItem('sp-theme', next); } catch (e) {}
            document.querySelectorAll('[data-sp-theme-toggle]').forEach(function (b) {
                b.setAttribute('aria-label', next === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode malam');
                b.setAttribute('title', next === 'dark' ? 'Mode terang' : 'Mode malam');
            });
            window.dispatchEvent(new CustomEvent('sp-theme', { detail: next }));
            setTimeout(function () { root.classList.remove('sp-theme-anim'); }, 300);
        };

        // Kelas bawaan AdminLTE untuk halaman lama, dipasang begitu <body> tersedia
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.toggle('dark-mode', document.documentElement.getAttribute('data-theme') === 'dark');
        });
    })();
</script>
<link rel="stylesheet" href="{{ asset('css/sipekan-dark.css') }}?v={{ filemtime(public_path('css/sipekan-dark.css')) }}">
