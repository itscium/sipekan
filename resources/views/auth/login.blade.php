<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Informasi Pelaporan dan Keuangan Uni Indonesia Kawasan Barat">
    <meta name="theme-color" content="#133c7a">
    <title>Masuk · SIPekan</title>
    <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sipekan.css') }}?v={{ filemtime(public_path('css/sipekan.css')) }}">
    @include('partials.sp-theme-head')
</head>
<body class="sp-body">
@include('sweetalert::alert')

<button type="button" class="sp-icon-btn sp-theme-toggle sp-auth-theme" data-sp-theme-toggle onclick="spToggleTheme()"
        aria-label="Ganti mode malam/terang" title="Mode malam/terang">
    <i class="fas fa-moon sp-theme-moon"></i>
    <i class="fas fa-sun sp-theme-sun"></i>
</button>

<div class="sp-auth">
    <section class="sp-auth-side sp-auth-side-3d">
        {{-- Animasi 3D (Three.js). Tanpa WebGL, panel tetap tampil dengan latar gradasi. --}}
        <div class="sp-auth-3d" id="spAuth3d" aria-hidden="true"></div>
        <div class="sp-auth-brand">
            <img src="{{ asset('vendor/adminlte/dist/img/adventist-symbol-circle-ming.png') }}" alt="GMAHK">
            SIPekan
        </div>

        <div class="sp-auth-hero" style="position:relative;z-index:1">
            <h1>Sistem Informasi Pelaporan dan Keuangan</h1>
            <p>Uni Indonesia Kawasan Barat. Lihat keuangan pribadi, slip gaji, absensi, dan laporan departemen di satu tempat.</p>
            <ul class="sp-auth-points">
                <li><i class="fas fa-wallet"></i>Saldo dan riwayat transaksi pribadi</li>
                <li><i class="fas fa-file-invoice-dollar"></i>Slip gaji yang bisa dicetak</li>
                <li><i class="fas fa-chart-bar"></i>Laporan anggaran departemen</li>
            </ul>
        </div>

        <div class="sp-auth-foot">&copy; {{ date('Y') }} Departemen IT CIUM</div>
    </section>

    <main class="sp-auth-main">
        <form class="sp-auth-form" action="{{ route('login') }}" method="post" novalidate>
            @csrf
            <div class="sp-auth-brand sp-auth-brand-mobile">
                <img src="{{ asset('vendor/adminlte/dist/img/adventist-symbol-circle-ming.png') }}" alt="GMAHK">
                SIPekan
            </div>
            <h2>Masuk</h2>
            <p class="sp-lead">Gunakan email dan password akun SIPekan Anda.</p>

            <div class="sp-field">
                <label for="email">Email</label>
                <div class="sp-input-wrap">
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="sp-input @error('email') is-invalid @enderror"
                           placeholder="nama@ciumsda.org" autocomplete="username" required autofocus>
                </div>
                @error('email')
                    <div class="sp-field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="sp-field">
                <label for="password">Password</label>
                <div class="sp-input-wrap">
                    <i class="fas fa-lock" aria-hidden="true"></i>
                    <input id="password" type="password" name="password"
                           class="sp-input @error('password') is-invalid @enderror"
                           autocomplete="current-password" required>
                    <button type="button" class="sp-input-toggle" id="spTogglePass" aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="sp-field-error">{{ $message }}</div>
                @enderror
            </div>

            <label class="sp-check">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Ingat saya di perangkat ini
            </label>

            <button type="submit" class="sp-btn sp-btn-primary">
                Masuk <i class="fas fa-arrow-right" style="font-size:13px"></i>
            </button>

            {{-- Disembunyikan jika halaman dibuka dari dalam aplikasi Android SIPekan sendiri --}}
            @php($apkPath = storage_path('app/' . config('sipekan.android.file')))
            @if(!str_contains(request()->userAgent() ?? '', 'SIPekanApp') && is_file($apkPath))
                <a href="{{ route('android.download') }}" class="sp-btn sp-btn-android" download>
                    <i class="fab fa-android"></i> Unduh Aplikasi Android
                </a>
                <p class="sp-android-meta">
                    Versi {{ config('sipekan.android.version') }} ·
                    {{ number_format(filesize($apkPath) / 1024, 0, ',', '.') }} KB ·
                    Android {{ config('sipekan.android.min_android') }} ke atas
                </p>
            @endif

            <p class="sp-auth-note">Lupa password? Hubungi bagian keuangan atau admin wilayah Anda.</p>
        </form>
    </main>
</div>

<script type="importmap">
    {
        "imports": {
            "three": "{{ asset('vendor/three/three.module.min.js') }}"
        }
    }
</script>
<script type="module" src="{{ asset('js/sipekan-login-3d.js') }}?v={{ filemtime(public_path('js/sipekan-login-3d.js')) }}"></script>
<script>
    document.getElementById('spTogglePass').addEventListener('click', function () {
        var p = document.getElementById('password');
        var show = p.type === 'password';
        p.type = show ? 'text' : 'password';
        this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        this.firstElementChild.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
</script>
</body>
</html>
