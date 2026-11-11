@extends('adminlte::master')

@php
    $spUser = Auth::user();
    $spWords = preg_split('/\s+/', trim($spUser->name ?? 'U'));
    $spInitials = strtoupper(mb_substr($spWords[0] ?? 'U', 0, 1) . (count($spWords) > 1 ? mb_substr(end($spWords), 0, 1) : ''));
    $spRole = $spUser->type === 'admin' ? 'Administrator' : (optional($spUser->wilayah)->nama ?? 'Pegawai');
    $spPageTitle = trim(preg_replace('/^\s*sipekan\s*\|\s*/i', '', View::getSection('title') ?? ''));
@endphp

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', 'sp-body')

@section('body')
    <div class="sp-app" id="spApp">

        {{-- Sidebar --}}
        <aside class="sp-sidebar" aria-label="Menu utama">
            <a href="{{ route('dashboard') }}" class="sp-brand">
                <img src="{{ asset(config('adminlte.logo_img')) }}" alt="GMAHK">
                <span>
                    <span class="sp-brand-name d-block">SIPekan</span>
                    <span class="sp-brand-sub">Uni Indonesia Kawasan Barat</span>
                </span>
            </a>

            <div class="sp-menu-search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="search" id="spMenuSearch" placeholder="Cari menu" autocomplete="off" aria-label="Cari menu">
            </div>

            <nav class="sp-nav">
                <ul id="spMenu">
                    @each('adminlte::partials.sidebar.sp-menu-item', $adminlte->menu('sidebar'), 'item')
                </ul>
                <div class="sp-nav-empty" id="spMenuEmpty">Menu tidak ditemukan.</div>
            </nav>

            <div class="sp-sidebar-user">
                <span class="sp-avatar">{{ $spInitials }}</span>
                <div style="min-width:0">
                    <div class="sp-user-name">{{ $spUser->name }}</div>
                    <div class="sp-user-meta">{{ $spRole }}</div>
                </div>
            </div>
        </aside>
        <div class="sp-backdrop" data-sp-close-sidebar></div>

        <div class="sp-main">
            {{-- Topbar --}}
            <header class="sp-topbar">
                <button type="button" class="sp-icon-btn sp-burger" data-sp-toggle-sidebar aria-label="Buka menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="sp-topbar-title">
                    <span class="sp-faint">SIPekan</span>
                    @if($spPageTitle)
                        <span class="sp-faint mx-1">/</span><span>{{ $spPageTitle }}</span>
                    @endif
                </div>

                <div class="sp-topbar-right">
                    @if(session()->has('impersonate'))
                        <form method="POST" action="{{ route('impersonate.destroy') }}" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sp-impersonate">
                                <i class="fas fa-user-secret"></i><span>Kembali ke akun admin</span>
                            </button>
                        </form>
                    @endif

                    <div class="sp-dropdown" id="spUserMenu">
                        <button type="button" class="sp-user-btn" data-sp-dropdown aria-haspopup="true" aria-expanded="false">
                            <span class="sp-avatar">{{ $spInitials }}</span>
                            <span class="sp-user-label sp-muted" style="font-size:13.5px;font-weight:500">{{ \Illuminate\Support\Str::limit($spUser->name, 22) }}</span>
                            <i class="fas fa-chevron-down sp-faint" style="font-size:11px"></i>
                        </button>
                        <div class="sp-dropdown-menu" role="menu">
                            <div class="sp-dropdown-head">
                                <div class="sp-user-name">{{ $spUser->name }}</div>
                                <div class="sp-user-meta">{{ $spUser->email }}</div>
                            </div>
                            @can('user')
                                <a href="{{ route('personal.profile.index') }}" class="sp-dropdown-item"><i class="fas fa-user"></i>Profil saya</a>
                            @endcan
                            <a href="{{ route('personal.change-password.index') }}" class="sp-dropdown-item"><i class="fas fa-lock"></i>Ganti password</a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="sp-dropdown-item danger"><i class="fas fa-sign-out-alt"></i>Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Konten --}}
            <main class="sp-content">
                @hasSection('content_header')
                    <div class="sp-page-header">
                        @yield('content_header')
                    </div>
                @endif
                @yield('content')
            </main>

            <footer class="sp-footer">
                <span>&copy; {{ date('Y') }} Uni Indonesia Kawasan Barat</span>
                <span>Dibuat oleh Departemen IT CIUM</span>
            </footer>
        </div>

        <x-notification-component/>
    </div>
@stop

@section('adminlte_js')
    <script>
        (function () {
            var app = document.getElementById('spApp');

            // Sidebar di layar kecil
            document.querySelectorAll('[data-sp-toggle-sidebar]').forEach(function (b) {
                b.addEventListener('click', function () { app.classList.toggle('sidebar-open'); });
            });
            document.querySelectorAll('[data-sp-close-sidebar]').forEach(function (b) {
                b.addEventListener('click', function () { app.classList.remove('sidebar-open'); });
            });

            // Submenu
            document.querySelectorAll('[data-sp-group]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var g = b.closest('.sp-nav-group');
                    g.classList.toggle('open');
                    b.setAttribute('aria-expanded', g.classList.contains('open'));
                });
            });

            // Menu pengguna
            var dd = document.getElementById('spUserMenu');
            dd.querySelector('[data-sp-dropdown]').addEventListener('click', function (e) {
                e.stopPropagation();
                dd.classList.toggle('open');
                this.setAttribute('aria-expanded', dd.classList.contains('open'));
            });
            document.addEventListener('click', function (e) {
                if (!dd.contains(e.target)) dd.classList.remove('open');
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') { dd.classList.remove('open'); app.classList.remove('sidebar-open'); }
            });

            // Pencarian menu
            var input = document.getElementById('spMenuSearch');
            var empty = document.getElementById('spMenuEmpty');
            input.addEventListener('input', function () {
                var q = input.value.trim().toLowerCase();
                var found = 0;
                document.querySelectorAll('#spMenu > li').forEach(function (li) {
                    if (li.classList.contains('sp-nav-header')) { li.style.display = q ? 'none' : ''; return; }
                    var subs = li.querySelectorAll('.sp-nav-sub > li');
                    if (subs.length) {
                        var any = false;
                        subs.forEach(function (s) {
                            var m = !q || s.textContent.toLowerCase().indexOf(q) > -1 || li.querySelector('[data-sp-group]').textContent.toLowerCase().indexOf(q) > -1;
                            s.style.display = m ? '' : 'none';
                            any = any || m;
                        });
                        li.style.display = any ? '' : 'none';
                        if (q && any) li.classList.add('open');
                        if (any) found++;
                    } else {
                        var m = !q || li.textContent.toLowerCase().indexOf(q) > -1;
                        li.style.display = m ? '' : 'none';
                        if (m) found++;
                    }
                });
                empty.style.display = found ? 'none' : 'block';
            });
        })();
    </script>
    @stack('js')
    @yield('js')
@stop
