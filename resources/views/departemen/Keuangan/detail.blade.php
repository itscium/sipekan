@extends('adminlte::page')

@section('title', 'SIPekan | Rincian keuangan departemen')

@php
    $bulan = \Illuminate\Support\Carbon::createFromFormat('Y-m', $periode)->locale('id');
    $q = array_filter(['departemen' => request('departemen'), 'periode' => $periode]);
@endphp

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
        <div>
            <h1>{{ ucwords(strtolower($allowance->nama)) }}</h1>
            <div class="sp-muted mt-1">
                {{ $departemen->nama_departemen }} · akun {{ $allowance->account_code }} · Januari – {{ $bulan->translatedFormat('F Y') }}
            </div>
        </div>
        <a href="{{ route('departemen.keuangan.index', $q) }}" class="sp-btn" style="height:38px"><i class="fas fa-arrow-left"></i>Kembali</a>
    </div>
@stop

@section('content')
    <div class="d-flex flex-wrap mb-3" style="gap:16px">
        <div class="sp-card" style="flex:1;min-width:220px">
            <div class="sp-stat-label"><span class="sp-stat-icon"><i class="fas fa-receipt"></i></span>Realisasi</div>
            <div class="sp-stat-value sp-num">{{ $saldo }}</div>
            <div class="sp-stat-foot">{{ count($detail) }} transaksi</div>
        </div>
        <form method="GET" class="sp-card" style="width:240px">
            @if(request('departemen'))<input type="hidden" name="departemen" value="{{ request('departemen') }}">@endif
            <label for="periode" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Sampai bulan</label>
            <input type="month" id="periode" name="periode" value="{{ $periode }}" class="form-control" onchange="this.form.submit()">
        </form>
    </div>

    <div class="card">
        <div class="card-body p-0 table-responsive" style="max-height:640px">
            <table class="table table-head-fixed mb-0">
                <thead>
                <tr>
                    <th>Periode</th>
                    <th>No. jurnal</th>
                    <th>Referensi</th>
                    <th>Keterangan</th>
                    <th class="text-right">Jumlah</th>
                </tr>
                </thead>
                <tbody>
                @forelse($detail as $item)
                    <tr>
                        <td class="sp-muted">{{ $item['period'] }}</td>
                        <td>{{ $item['nomor_jurnal'] }}</td>
                        <td>{{ $item['reference'] }}</td>
                        <td>{{ $item['description'] }}</td>
                        <td class="text-right sp-num">{{ $item['amount'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center sp-faint py-4">Belum ada transaksi pada periode ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
