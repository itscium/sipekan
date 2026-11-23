@extends('adminlte::page')

@section('title', 'SIPekan | Laporan keuangan dipindahkan')

@php
    $url = $announcement && $announcement->button_url ? $announcement->button_url : config('sipekan.portal.url');
    $btn = $announcement && $announcement->button_text ? $announcement->button_text : 'Buka ' . config('sipekan.portal.name');
@endphp

@section('content')
    <div class="sp-card sp-locked" style="max-width:640px;margin:40px auto 0">
        <span class="sp-locked-ico" aria-hidden="true"><i class="fas fa-lock"></i></span>
        <h1 style="font-size:22px;margin:0">Laporan keuangan dipindahkan ke {{ config('sipekan.portal.name') }}</h1>
        <p class="sp-muted" style="max-width:52ch;margin:0">
            Saldo, transaksi, slip gaji, dan riwayat gaji Anda sekarang tersedia di portal yang baru.
            SIPekan tidak lagi menampilkan laporan keuangan untuk wilayah Anda.
        </p>
        <a href="{{ $url }}" target="_blank" rel="noopener" class="sp-btn sp-btn-primary">{{ $btn }} <i class="fas fa-external-link-alt" style="font-size:12px"></i></a>
        <span class="sp-faint" style="font-size:12.5px">{{ $url }}</span>
        <a href="{{ route('dashboard') }}" style="font-size:13.5px">Kembali ke Beranda</a>
    </div>
@stop
