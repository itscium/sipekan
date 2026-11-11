@extends('adminlte::page')

@section('title', 'SIPekan | Beranda')

@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $hour = (int) now()->format('H');
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
    $firstName = explode(' ', trim($user->name))[0];
    \Illuminate\Support\Carbon::setLocale('id');
@endphp

@section('content_header')
    <div class="d-flex flex-wrap align-items-end justify-content-between" style="gap:12px">
        <div>
            <h1>{{ $greeting }}, {{ $firstName }}</h1>
            <div class="sp-muted mt-1">{{ now()->translatedFormat('l, d F Y') }}</div>
        </div>
    </div>
@stop

@section('content')

    @if(!empty($d['errors']))
        <div class="sp-alert mb-3">
            <i class="fas fa-exclamation-triangle mt-1"></i>
            <div>Sebagian data belum bisa dimuat dari server keuangan. Coba muat ulang halaman dalam beberapa menit.</div>
        </div>
    @endif

    @if($d['is_admin'])
        {{-- ================= Beranda admin ================= --}}
        @php($a = $d['admin'])
        @if($a)
            <div class="sp-grid sp-grid-stats mb-3">
                <div class="sp-card">
                    <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-users"></i></span>Pengguna aktif</div>
                    <div class="sp-stat-value sp-num">{{ number_format($a['users_active'], 0, ',', '.') }}</div>
                    <div class="sp-stat-foot">{{ number_format($a['users_inactive'], 0, ',', '.') }} non-aktif</div>
                </div>
                <div class="sp-card">
                    <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-map"></i></span>Wilayah</div>
                    <div class="sp-stat-value sp-num">{{ $a['wilayah'] }}</div>
                    <div class="sp-stat-foot">Konferens dan misi</div>
                </div>
                <div class="sp-card">
                    <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-building"></i></span>Departemen</div>
                    <div class="sp-stat-value sp-num">{{ $a['departemen'] }}</div>
                    <div class="sp-stat-foot">Di semua wilayah</div>
                </div>
                <div class="sp-card">
                    <div class="sp-stat-label"><span class="sp-stat-icon" style="background:var(--sp-warning-soft);color:var(--sp-warning)"><i class="fas fa-user-tag"></i></span>Tanpa kode akun</div>
                    <div class="sp-stat-value sp-num">{{ number_format($a['users_no_account'], 0, ',', '.') }}</div>
                    <div class="sp-stat-foot">Pengguna aktif belum punya kode akun</div>
                </div>
            </div>

            {{-- Siapa yang sedang mengakses SIPekan + log aktivitas (khusus admin) --}}
            @include('partials.sp-activity-panel')

            <div class="sp-grid sp-grid-2">
                <div class="sp-card">
                    <div class="sp-card-head">
                        <div>
                            <p class="sp-card-title">Pengguna aktif per wilayah</p>
                            <p class="sp-card-sub">Enam wilayah dengan pengguna terbanyak</p>
                        </div>
                        <a href="{{ route('wilayah.index') }}" class="small">Lihat semua</a>
                    </div>
                    @php($max = max(1, optional($a['wilayah_list']->first())->users_count ?? 1))
                    @foreach($a['wilayah_list'] as $w)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between" style="font-size:13.5px">
                                <a href="{{ route('wilayah.pengguna', $w->id) }}" style="color:var(--sp-text)">{{ $w->nama }}</a>
                                <span class="sp-num sp-muted">{{ $w->users_count }}</span>
                            </div>
                            <div class="sp-progress"><span style="width: {{ round($w->users_count / $max * 100) }}%"></span></div>
                        </div>
                    @endforeach
                </div>
                <div class="sp-card">
                    <p class="sp-card-title mb-3">Akses cepat</p>
                    <div class="sp-shortcuts" style="grid-template-columns:1fr">
                        <a href="{{ route('wilayah.index') }}" class="sp-shortcut"><i class="fas fa-map"></i>Kelola wilayah dan pengguna</a>
                        <a href="{{ route('employee.allowance.index') }}" class="sp-shortcut"><i class="fas fa-users"></i>Tunjangan pegawai</a>
                        <a href="{{ route('departemen.keuangan.index') }}" class="sp-shortcut"><i class="fas fa-cubes"></i>Tunjangan departemen</a>
                        <a href="{{ route('sop.index') }}" class="sp-shortcut"><i class="fas fa-book"></i>SOP</a>
                    </div>
                </div>
            </div>
        @endif

    @else
        {{-- ================= Beranda pegawai ================= --}}
        @php($p = $d['personal'])
        @php($t = $d['travel'])
        @php($att = $d['attendance'])

        <div class="sp-grid sp-grid-stats mb-3">
            <div class="sp-card">
                <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-wallet"></i></span>Saldo akun personal</div>
                @if($p)
                    <div class="sp-stat-value sp-num">
                        {{ $rp($p['balance']) }}
                        @if($p['is_credit'])<span class="sp-badge accent align-middle" title="Saldo kredit">Cr.</span>@endif
                    </div>
                    <div class="sp-stat-foot">Bulan ini: debit {{ number_format($p['month_debit'], 0, ',', '.') }} · kredit {{ number_format($p['month_credit'], 0, ',', '.') }}</div>
                @else
                    <div class="sp-stat-value sp-faint">–</div>
                    <div class="sp-stat-foot">Kode akun belum diatur</div>
                @endif
            </div>

            @if($t)
                <div class="sp-card">
                    <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-plane"></i></span>Saldo akun travel</div>
                    <div class="sp-stat-value sp-num">
                        {{ $rp($t['balance']) }}
                        @if($t['is_credit'])<span class="sp-badge accent align-middle" title="Saldo kredit">Cr.</span>@endif
                    </div>
                    <div class="sp-stat-foot"><a href="{{ route('personal.keuangan.travel') }}">Lihat rincian travel</a></div>
                </div>
            @endif

            @if($att)
                @php($pct = min(100, round($att['present'] / $att['workdays'] * 100)))
                <div class="sp-card">
                    <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-fingerprint"></i></span>Kehadiran {{ now()->translatedFormat('F') }}</div>
                    @if($att['stale'])
                        <div class="sp-stat-value sp-faint">–</div>
                        <div class="sp-stat-foot">
                            <span class="sp-badge warning">Belum ada data bulan ini</span>
                            <div class="mt-1">Data mesin terakhir {{ $att['last_sync'] ? $att['last_sync']->translatedFormat('d M Y') : 'tidak ada' }}</div>
                        </div>
                    @else
                    <div class="sp-stat-value sp-num">{{ $att['present'] }} <span class="sp-faint" style="font-size:16px;font-weight:500">/ {{ $att['workdays'] }} hari kerja</span></div>
                    <div class="sp-progress"><span style="width: {{ $pct }}%"></span></div>
                    <div class="sp-stat-foot mt-2">
                        @if($att['late'] > 0)
                            <span class="sp-badge warning">{{ $att['late'] }}× terlambat</span>
                        @else
                            <span class="sp-badge success">Tidak pernah terlambat</span>
                        @endif
                    </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- ================= Riwayat gaji dengan filter bulan ================= --}}
        @include('partials.sp-payroll', ['pay' => $pay, 'payRoute' => 'dashboard', 'payAnchor' => 'riwayat-gaji'])


        <div class="sp-grid sp-grid-2">
            <div class="sp-card">
                <div class="sp-card-head">
                    <div>
                        <p class="sp-card-title">Transaksi terakhir</p>
                        <p class="sp-card-sub">Akun personal</p>
                    </div>
                    <a href="{{ route('personal.keuangan.index') }}" class="small">Lihat semua</a>
                </div>
                @if($p && count($p['transactions']))
                    <ul class="sp-list">
                        @foreach($p['transactions'] as $tx)
                            <li>
                                <span class="sp-stat-icon" style="{{ $tx['type'] === 'credit' ? 'background:var(--sp-success-soft);color:var(--sp-success)' : 'background:var(--sp-danger-soft);color:var(--sp-danger)' }}">
                                    <i class="fas {{ $tx['type'] === 'credit' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                </span>
                                <div class="sp-list-main">
                                    <div class="sp-list-title" title="{{ $tx['description'] }}">{{ $tx['description'] }}</div>
                                    <div class="sp-list-sub">{{ $tx['date']->translatedFormat('d M Y') }} · Jurnal {{ $tx['journal'] }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="sp-amount {{ $tx['type'] === 'credit' ? 'in' : 'out' }}">{{ number_format($tx['amount'], 0, ',', '.') }}</div>
                                    <div class="sp-list-sub">{{ $tx['type'] === 'credit' ? 'Kredit' : 'Debit' }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="sp-empty">Belum ada transaksi.</div>
                @endif
            </div>

            <div>
                @if($d['department'])
                    <div class="sp-card mb-3">
                        <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-building"></i></span>Departemen Anda</div>
                        <p class="sp-card-title mt-3 mb-1">{{ $d['department']->nama_departemen }}</p>
                        <p class="sp-card-sub mb-3">Pantau anggaran travel, kantor, dan strategic plan.</p>
                        <a href="{{ route('departemen.keuangan.index') }}" class="sp-btn sp-btn-primary" style="height:36px;font-size:13.5px">
                            Buka keuangan departemen <i class="fas fa-arrow-right" style="font-size:12px"></i>
                        </a>
                    </div>
                @endif

                <div class="sp-card">
                    <p class="sp-card-title mb-3">Akses cepat</p>
                    <div class="sp-shortcuts" style="grid-template-columns:1fr">
                        <a href="{{ route('personal.keuangan.index') }}" class="sp-shortcut"><i class="fas fa-wallet"></i>Keuangan personal</a>
                        @can('ada_travel')
                            <a href="{{ route('personal.keuangan.travel') }}" class="sp-shortcut"><i class="fas fa-plane"></i>Keuangan travel</a>
                        @endcan
                        @can('uni')
                            <a href="{{ route('personal.attendance.index') }}" class="sp-shortcut"><i class="fas fa-fingerprint"></i>Absensi</a>
                        @endcan
                        @canany(['finance', 'officers'])
                            <a href="{{ route('report.departemen') }}" class="sp-shortcut"><i class="fas fa-chart-bar"></i>Pengeluaran departemen</a>
                        @endcanany
                        @can('conference_finance')
                            <a href="{{ route('ledger.conference_on_wium.index') }}" class="sp-shortcut"><i class="fas fa-folder-open"></i>Ledger konferens di WIUM</a>
                        @endcan
                        <a href="{{ route('personal.profile.index') }}" class="sp-shortcut"><i class="fas fa-user"></i>Profil saya</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop
