@extends('adminlte::page')

@section('title', 'SIPekan | Profil')

@php
    \Illuminate\Support\Carbon::setLocale('id');
    $dept = $dept ?? collect();
    $departemen = $user->kepala_departemen->nama_departemen ?? null;
    $ttl = collect([$user->tempat_lahir, $user->tanggal_lahir ? \Illuminate\Support\Carbon::parse($user->tanggal_lahir)->translatedFormat('d F Y') : null])->filter()->implode(', ');
    $kosong = '<span class="sp-faint">Belum diisi</span>';
    $showPay = $cek !== null || ($pay['available'] ?? false) || ($pay['error'] ?? false);
@endphp

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
        <h1>Profil saya</h1>
        <a href="{{ route('personal.profile.edit') }}" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-pen"></i>Edit profil</a>
    </div>
@stop

@section('content')
    <div class="sp-profile">
        {{-- Kolom kiri --}}
        <div>
            <div class="sp-card text-center mb-3">
                <div class="sp-profile-photo">
                    @include('partials.sp-avatar', ['u' => $user, 'size' => 112])
                    <a href="{{ route('personal.profile.edit') }}#foto" class="sp-profile-photo-edit" title="Ganti foto" aria-label="Ganti foto"><i class="fas fa-camera"></i></a>
                </div>
                <p class="sp-card-title mt-3" style="font-size:17px">{{ $user->name }}</p>
                @if($departemen)<p class="sp-muted mb-1" style="font-size:13.5px">{{ $departemen }}</p>@endif
                <p class="sp-faint mb-0" style="font-size:13px">{{ optional($user->wilayah)->nama }}</p>
            </div>

            <div class="sp-card">
                <p class="sp-card-title mb-3">Tentang saya</p>

                <div class="sp-about-item">
                    <i class="fas fa-graduation-cap"></i>
                    <div><div class="sp-about-label">Pendidikan</div>{!! $user->pendidikan ? e($user->pendidikan) : $kosong !!}</div>
                </div>
                <div class="sp-about-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div><div class="sp-about-label">Alamat</div>{!! $user->alamat ? e($user->alamat) : $kosong !!}</div>
                </div>
                <div class="sp-about-item">
                    <i class="fas fa-star"></i>
                    <div>
                        <div class="sp-about-label">Keahlian</div>
                        @forelse($user->keahlian_list as $skill)
                            <span class="sp-badge accent mr-1 mb-1">{{ $skill }}</span>
                        @empty
                            {!! $kosong !!}
                        @endforelse
                    </div>
                </div>
                <div class="sp-about-item">
                    <i class="far fa-file-alt"></i>
                    <div><div class="sp-about-label">Catatan</div>{!! $user->tentang ? nl2br(e($user->tentang)) : $kosong !!}</div>
                </div>
            </div>
        </div>

        {{-- Kolom kanan --}}
        <div class="sp-card p-0">
            <div class="sp-tabs nav" role="tablist">
                <a class="sp-tab active" href="#identitas" data-toggle="tab" role="tab">Data pribadi</a>
                @if($showPay)
                    <a class="sp-tab" href="#tunjangan" data-toggle="tab" role="tab">Gaji dan tunjangan</a>
                @endif
                @if($cek !== null)
                    @can('head_dept')
                        <a class="sp-tab" href="#anggaran" data-toggle="tab" role="tab">Anggaran departemen</a>
                    @endcan
                @endif
            </div>

            <div class="tab-content" style="padding:8px 20px 20px">
                <div class="tab-pane active" id="identitas" role="tabpanel">
                    <dl class="sp-dl">
                        <dt>Nama lengkap</dt><dd>{{ $user->name }}</dd>
                        <dt>Email</dt><dd>{{ $user->email }}</dd>
                        <dt>No. HP</dt><dd>{!! $user->no_hp ? e($user->no_hp) : $kosong !!}</dd>
                        <dt>Jenis kelamin</dt><dd>{!! $user->jenis_kelamin ? e($user->jenis_kelamin) : $kosong !!}</dd>
                        <dt>Tempat, tanggal lahir</dt><dd>{!! $ttl !== '' ? e($ttl) : $kosong !!}</dd>
                        <dt>Alamat</dt><dd>{!! $user->alamat ? e($user->alamat) : $kosong !!}</dd>
                        <dt>Wilayah</dt><dd>{{ optional($user->wilayah)->nama ?? '–' }}</dd>
                        <dt>Departemen</dt><dd>{{ $departemen ?? '–' }}</dd>
                        <dt>Kode akun</dt><dd><code>{{ $user->ACCNT_CODE ?? '–' }}</code></dd>
                        @if($user->travel_account)
                            <dt>Akun travel</dt><dd><code>{{ $user->travel_account }}</code></dd>
                        @endif
                    </dl>
                    <p class="sp-faint mb-0" style="font-size:12.5px">
                        <i class="fas fa-lock mr-1"></i>Nama, email, wilayah, dan kode akun diatur oleh admin. Hubungi admin jika ada yang perlu diubah.
                    </p>
                </div>

                @if($showPay)
                    <div class="tab-pane" id="tunjangan" role="tabpanel">
                        {{-- Riwayat gaji dari payroll, sama seperti di Beranda --}}
                        <div class="sp-pay-embed">
                            @include('partials.sp-payroll', ['pay' => $pay, 'payRoute' => 'personal.profile.index', 'payAnchor' => 'tunjangan'])
                        </div>

                        @if(!($pay['available'] ?? false) && !($pay['error'] ?? false))
                            <div class="sp-empty">Belum ada data gaji untuk akun Anda.</div>
                        @endif

                        @if($salary->isNotEmpty() || $monthly->isNotEmpty())
                            <hr style="border-color:var(--sp-border);margin:8px 0 4px">
                            <div class="row">
                                <div class="col-lg-6">
                                    <p class="sp-pay-items-title mt-3">Tunjangan sesuai laporan ({{ date('Y') }})</p>
                                    @forelse($salary as $item)
                                        <div class="sp-pay-row">
                                            <span>{{ $item->allowances->nama }}@if($item->keterangan) <span class="sp-faint">· {{ $item->keterangan }}</span>@endif</span>
                                            <span class="sp-num">{{ number_format($item->jumlah, 0, ',', '.') }}{{ $item->satuan ?? '' }}</span>
                                        </div>
                                    @empty
                                        <div class="sp-faint" style="font-size:13px">Tidak ada data.</div>
                                    @endforelse
                                </div>
                                <div class="col-lg-6">
                                    <p class="sp-pay-items-title mt-3">Tunjangan bulanan ({{ date('Y') }})</p>
                                    @forelse($monthly as $item)
                                        <div class="sp-pay-row">
                                            <span>{{ $item->allowances->nama }}</span>
                                            <span class="sp-num">{{ number_format($item->jumlah, 0, ',', '.') }}{{ $item->satuan ?? '' }}</span>
                                        </div>
                                    @empty
                                        <div class="sp-faint" style="font-size:13px">Tidak ada data.</div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if($cek !== null)
                    @can('head_dept')
                        <div class="tab-pane" id="anggaran" role="tabpanel">
                            <p class="sp-pay-items-title mt-3">Anggaran departemen ({{ date('Y') }})</p>
                            @forelse($dept as $item)
                                <div class="sp-pay-row">
                                    <span>{{ $item->allowances->nama }}@if($item->keterangan) <span class="sp-faint">· {{ $item->keterangan }}</span>@endif</span>
                                    <span class="sp-num">{{ number_format($item->jumlah, 0, ',', '.') }}{{ $item->satuan ?? '' }}</span>
                                </div>
                            @empty
                                <div class="sp-faint" style="font-size:13px">Tidak ada data.</div>
                            @endforelse
                        </div>
                    @endcan
                @endif
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        // Buka tab sesuai alamat, mis. /personal/profile?gaji=2025-01#tunjangan
        (function () {
            var h = window.location.hash;
            if (h && document.querySelector('.sp-tabs a[href="' + h + '"]')) {
                $('.sp-tabs a[href="' + h + '"]').tab('show');
            }
            $('.sp-tabs a').on('shown.bs.tab', function (e) {
                history.replaceState(null, '', e.target.getAttribute('href'));
            });
        })();
    </script>
@endpush
