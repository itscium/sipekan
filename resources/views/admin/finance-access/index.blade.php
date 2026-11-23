@extends('adminlte::page')

@section('title', 'SIPekan | Akses keuangan')

@section('content_header')
    <h1>Akses laporan keuangan</h1>
    <div class="sp-muted mt-1" style="max-width:80ch">
        Jika ditutup, pegawai di Business Unit itu tidak bisa lagi membuka Keuangan personal, Keuangan travel, slip gaji,
        riwayat gaji, saldo di Beranda, dan Keuangan departemen. Mereka diarahkan ke {{ config('sipekan.portal.name') }}.
        Mereka tetap bisa masuk dan membaca pengumuman. Admin tidak terpengaruh.
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table mb-0">
                <thead>
                <tr><th>Business Unit</th><th>Kode</th><th class="text-right">Pengguna aktif</th><th>Laporan keuangan</th><th class="text-right">Tindakan</th></tr>
                </thead>
                <tbody>
                @foreach($wilayah as $w)
                    @php($lock = $locks[$w->id] ?? null)
                    <tr>
                        <td><strong style="font-weight:500">{{ $w->nama }}</strong></td>
                        <td><span class="sp-badge accent">{{ $w->short_code }}</span></td>
                        <td class="text-right sp-num">{{ $userCounts[$w->id] ?? 0 }}</td>
                        <td>
                            @if($lock)
                                <span class="sp-badge danger">Ditutup</span>
                                <div class="sp-faint" style="font-size:12px">
                                    {{ $lock->created_at ? $lock->created_at->locale('id')->translatedFormat('d M Y, H.i') : '' }}
                                    @if($lock->locker) · {{ $lock->locker->name }} @endif
                                </div>
                            @else
                                <span class="sp-badge success">Terbuka</span>
                            @endif
                        </td>
                        <td class="text-right" style="white-space:nowrap">
                            @if($lock)
                                <form action="{{ route('admin.finance-access.unlock', $w) }}" method="post" class="d-inline">@csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-lock-open"></i> Buka kembali</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-danger" data-lock-open="{{ $w->id }}" aria-expanded="false" aria-controls="lockForm{{ $w->id }}">
                                    <i class="fas fa-lock"></i> Tutup akses
                                </button>
                            @endif
                        </td>
                    </tr>
                    @unless($lock)
                        <tr id="lockForm{{ $w->id }}" hidden>
                            <td colspan="5" style="background:var(--sp-danger-soft)">
                                <form action="{{ route('admin.finance-access.lock', $w) }}" method="post" class="d-flex flex-column" style="gap:10px">
                                    @csrf
                                    <strong>Tutup laporan keuangan untuk {{ $w->nama }} ({{ $w->short_code }})?</strong>
                                    <ul class="mb-0 pl-3" style="font-size:13.5px">
                                        <li>Menu Keuangan dan slip gaji disembunyikan untuk {{ $userCounts[$w->id] ?? 0 }} pengguna aktif.</li>
                                        <li>Kartu saldo dan riwayat gaji di Beranda diganti pemberitahuan ke {{ config('sipekan.portal.name') }}.</li>
                                        <li>Membuka alamat halaman keuangan secara langsung juga ditolak.</li>
                                        <li>Bisa dibuka kembali kapan saja dari halaman ini.</li>
                                    </ul>
                                    <label class="sp-check m-0"><input type="checkbox" name="konfirmasi" value="1" required> Saya mengerti dan ingin menutup akses ini</label>
                                    <div class="d-flex" style="gap:8px">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-lock"></i> Ya, tutup akses</button>
                                        <button type="button" class="btn btn-default btn-sm" data-lock-cancel="{{ $w->id }}">Batal</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endunless
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <p class="sp-faint" style="font-size:12.5px"><i class="fas fa-history mr-1"></i>Setiap penutupan dan pembukaan kembali tercatat di <a href="{{ route('admin.activity.index', ['aksi' => 'lock']) }}">Log aktivitas</a>.</p>
@stop

@push('js')
<script>
document.querySelectorAll('[data-lock-open]').forEach(function (b) {
    b.addEventListener('click', function () {
        var row = document.getElementById('lockForm' + b.dataset.lockOpen);
        row.hidden = !row.hidden;
        b.setAttribute('aria-expanded', String(!row.hidden));
    });
});
document.querySelectorAll('[data-lock-cancel]').forEach(function (b) {
    b.addEventListener('click', function () {
        document.getElementById('lockForm' + b.dataset.lockCancel).hidden = true;
        var open = document.querySelector('[data-lock-open="' + b.dataset.lockCancel + '"]');
        if (open) open.setAttribute('aria-expanded', 'false');
    });
});
</script>
@endpush
