@extends('adminlte::page')

@section('title', 'SIPekan | Log aktivitas')

@php
    $badge = ['login' => 'success', 'logout' => '', 'failed' => 'danger', 'page' => '', 'print' => 'accent'];
@endphp

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
        <div>
            <h1>Log aktivitas</h1>
            <div class="sp-muted mt-1">Siapa membuka apa di SIPekan. Log disimpan 90 hari.</div>
        </div>
        <a href="{{ route('dashboard') }}" class="sp-btn" style="height:38px"><i class="fas fa-arrow-left"></i>Beranda</a>
    </div>
@stop

@section('content')
    <form method="GET" class="sp-card mb-3 d-flex flex-wrap align-items-end" style="gap:12px">
        <div style="flex:1;min-width:200px">
            <label for="q" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Cari</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Nama, email, atau IP">
        </div>
        <div style="width:170px">
            <label for="aksi" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Aktivitas</label>
            <select id="aksi" name="aksi" class="custom-select">
                <option value="">Semua</option>
                @foreach($actions as $k => $v)
                    <option value="{{ $k }}" {{ ($filters['aksi'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div style="width:160px">
            <label for="dari" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Dari</label>
            <input type="date" id="dari" name="dari" value="{{ $filters['dari'] ?? '' }}" class="form-control">
        </div>
        <div style="width:160px">
            <label for="sampai" class="d-block mb-1" style="font-size:13px;font-weight:500;color:var(--sp-text-2)">Sampai</label>
            <input type="date" id="sampai" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="form-control">
        </div>
        <div class="d-flex" style="gap:8px">
            <button type="submit" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-search"></i>Tampilkan</button>
            @if(array_filter($filters))
                <a href="{{ route('admin.activity.index') }}" class="sp-btn" style="height:38px">Reset</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th style="width:150px">Waktu</th>
                    <th>Pengguna</th>
                    <th>Aktivitas</th>
                    <th>Perangkat</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $l)
                    <tr>
                        <td class="sp-muted sp-num" style="white-space:nowrap">{{ $l->created_at->format('d/m/Y H.i') }}</td>
                        <td>
                            @if($l->user)
                                <strong style="font-weight:500">{{ $l->user->name }}</strong>
                            @else
                                <span class="sp-faint">Tidak dikenal</span>
                            @endif
                            @if($l->action === 'failed' && $l->email)
                                <div class="sp-faint" style="font-size:12px">{{ $l->email }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="sp-badge {{ $badge[$l->action] ?? '' }} mr-1">{{ $actions[$l->action] ?? $l->action }}</span>
                            {{ $l->sentence }}
                        </td>
                        <td class="sp-muted" style="white-space:nowrap">{{ $l->device }}</td>
                        <td class="sp-muted sp-num">{{ $l->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center sp-faint py-4">Belum ada log yang cocok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
            <span class="sp-faint" style="font-size:13px">{{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ number_format($logs->total(), 0, ',', '.') }} log</span>
            {{ $logs->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif
@stop
