@extends('adminlte::page')

@section('title', 'SIPekan | Keuangan departemen')

@php
    $bulan = \Illuminate\Support\Carbon::createFromFormat('Y-m', $periode)->locale('id');
    $q = array_filter(['departemen' => request('departemen'), 'periode' => $periode]);
@endphp

@section('content_header')
    <h1>Keuangan departemen</h1>
    <div class="sp-muted mt-1">
        @if($departemen)
            {{ $departemen->nama_departemen }}
            @if($departemen->wilayah)· {{ $departemen->wilayah->nama }}@endif
            · Januari – {{ $bulan->translatedFormat('F Y') }}
        @else
            Anggaran dan realisasi pengeluaran departemen.
        @endif
    </div>
@stop

@section('content')
    {{-- Filter --}}
    <form method="GET" class="sp-card mb-3 d-flex flex-wrap align-items-end" style="gap:12px">
        @if($isAdmin)
            <div style="width:260px">
                <label for="wilayah" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Wilayah</label>
                {{-- Ganti wilayah: kosongkan pilihan departemen agar daftar departemen ikut berganti --}}
                <select id="wilayah" name="wilayah" class="custom-select"
                        onchange="this.form.departemen.value='';this.form.submit()">
                    @foreach($daftar_wilayah as $w)
                        <option value="{{ $w->id }}" {{ (string) $wilayah_id === (string) $w->id ? 'selected' : '' }}>
                            {{ $w->nama }}{{ $w->id == 20003 ? ' (WIUM)' : ($w->id == 1 ? ' (CIUM)' : '') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:240px">
                <label for="departemen" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Departemen</label>
                <select id="departemen" name="departemen" class="custom-select" onchange="this.form.submit()">
                    <option value="">Pilih departemen ({{ $daftar_departemen->count() }})</option>
                    @foreach($daftar_departemen as $d)
                        <option value="{{ $d->id }}" {{ optional($departemen)->id == $d->id ? 'selected' : '' }}>{{ $d->nama_departemen }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div style="width:200px">
            <label for="periode" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Sampai bulan</label>
            <input type="month" id="periode" name="periode" value="{{ $periode }}" class="form-control" onchange="this.form.submit()">
        </div>
    </form>

    @if(!$departemen)
        <div class="sp-card text-center py-5">
            <i class="fas fa-building fa-2x sp-faint mb-3"></i>
            @if($isAdmin)
                <p class="sp-card-title">Pilih departemen</p>
                <p class="sp-card-sub mb-0">Pilih departemen di atas untuk melihat anggaran dan realisasinya.</p>
            @else
                <p class="sp-card-title">Anda belum terdaftar sebagai kepala departemen</p>
                <p class="sp-card-sub mb-0">Halaman ini menampilkan anggaran departemen yang Anda pimpin. Jika seharusnya Anda kepala departemen, hubungi admin wilayah untuk mengaturnya.</p>
            @endif
        </div>
    @elseif(empty($data_allowance))
        <div class="sp-card text-center py-5">
            <i class="fas fa-list-alt fa-2x sp-faint mb-3"></i>
            <p class="sp-card-title">Belum ada jenis anggaran untuk wilayah ini</p>
            <p class="sp-card-sub mb-0">Admin perlu menambahkan Department Expense (mis. Travel, Office) di menu Wilayah.</p>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th>Jenis anggaran</th>
                        <th>Kode akun</th>
                        <th class="text-right">Anggaran</th>
                        <th class="text-right">Realisasi</th>
                        <th class="text-right">Uang muka travel</th>
                        <th class="text-right">Sisa anggaran</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data_allowance as $item)
                        <tr>
                            <td><strong style="font-weight:500">{{ ucwords(strtolower($item['account_name'])) }}</strong></td>
                            <td><code style="color:var(--sp-text);background:var(--sp-surface-2);padding:2px 6px;border-radius:6px">{{ $item['account_code'] }}</code></td>
                            <td class="text-right sp-num">{{ $item['budget'] }}</td>
                            <td class="text-right sp-num">{{ $item['actual'] }}</td>
                            <td class="text-right sp-num">{{ $item['account_code'] === '822110' ? $item['travel_advance'] : '–' }}</td>
                            <td class="text-right sp-num"><strong style="font-weight:600">{{ $item['sisa'] }}</strong></td>
                            <td class="text-right">
                                <a href="{{ route('departemen.keuangan.detail', ['id' => $item['id']] + $q) }}" class="btn btn-primary btn-sm">Rincian</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <p class="sp-faint" style="font-size:12.5px">
            <i class="fas fa-info-circle mr-1"></i>
            Anggaran dihitung untuk satu tahun penuh; realisasi dari Januari sampai bulan terpilih.
            Untuk akun travel (822110), sisa anggaran juga dikurangi uang muka travel kepala departemen.
        </p>
    @endif
@stop
