@extends('adminlte::page')

@section('title', 'SIPekan | Pengumuman')

@php
    $statusChip = ['draf' => ['', 'Draf'], 'terjadwal' => ['warning', 'Terjadwal'], 'tayang' => ['success', 'Tayang'], 'selesai' => ['', 'Selesai']];
@endphp

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
        <div>
            <h1>Pengumuman</h1>
            <div class="sp-muted mt-1">Pengumuman tampil di Beranda pegawai pada Business Unit yang dipilih.</div>
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-plus"></i>Tulis pengumuman</a>
    </div>
@stop

@section('content')
    @if($announcements->isEmpty())
        <div class="sp-card text-center py-5">
            <i class="fas fa-bullhorn fa-2x sp-faint mb-3"></i>
            <p class="sp-card-title">Belum ada pengumuman</p>
            <p class="sp-card-sub mb-3">Tulis pengumuman pertama, mis. pemberitahuan pindah ke {{ config('sipekan.portal.name') }}.</p>
            <a href="{{ route('admin.announcements.create') }}" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-plus"></i>Tulis pengumuman</a>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr><th>Judul</th><th>Business Unit tujuan</th><th>Tayang</th><th>Status</th><th class="text-right">Opsi</th></tr>
                    </thead>
                    <tbody>
                    @foreach($announcements as $a)
                        @php($ids = array_map('intval', $a->target_wilayah ?? []))
                        <tr>
                            <td style="min-width:240px">
                                <strong style="font-weight:600">{{ $a->title }}</strong>
                                <div class="sp-faint" style="font-size:12.5px">
                                    <span class="sp-badge {{ $a->level === 'penting' ? 'warning' : 'accent' }}">{{ $a->level === 'penting' ? 'Penting' : 'Informasi' }}</span>
                                    {{ $a->display === 'both' ? 'Pop-up + banner' : 'Banner saja' }}
                                </div>
                            </td>
                            <td style="max-width:280px">
                                @if(count($ids) === $wilayah->count())
                                    <span class="sp-badge accent">Semua Business Unit</span>
                                @else
                                    @foreach($ids as $id)
                                        @if($wilayah->has($id))<span class="sp-badge accent mb-1" title="{{ $wilayah[$id]->nama }}">{{ $wilayah[$id]->short_code }}</span>@endif
                                    @endforeach
                                @endif
                            </td>
                            <td class="sp-num" style="white-space:nowrap">{{ $a->starts_at->format('d/m/Y') }} – {{ $a->ends_at ? $a->ends_at->format('d/m/Y') : 'tanpa batas' }}</td>
                            <td><span class="sp-badge {{ $statusChip[$a->status][0] }}">{{ $statusChip[$a->status][1] }}</span></td>
                            <td class="text-right" style="white-space:nowrap">
                                <form action="{{ route('admin.announcements.toggle', $a) }}" method="post" class="d-inline">@csrf
                                    <button type="submit" class="btn btn-sm {{ $a->is_published ? 'btn-secondary' : 'btn-success' }}">
                                        <i class="fas {{ $a->is_published ? 'fa-eye-slash' : 'fa-paper-plane' }}"></i> {{ $a->is_published ? 'Tarik' : 'Tayangkan' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.announcements.edit', $a) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Ubah</a>
                                <form action="{{ route('admin.announcements.destroy', $a) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus pengumuman ini?')">@csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@stop
