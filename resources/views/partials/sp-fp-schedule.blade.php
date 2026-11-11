{{-- Pemberitahuan jadwal tarik log absensi otomatis + hasil penarikan terakhir --}}
@php
    $fpLast = \Illuminate\Support\Facades\Cache::get(\App\Services\FingerprintSyncService::STATUS_KEY);
    $fpNext = \App\Services\FingerprintSyncService::nextRun()->locale('id');
    $fpLastAt = $fpLast ? \Illuminate\Support\Carbon::parse($fpLast['finished_at'])->timezone(config('app.timezone'))->locale('id') : null;
@endphp
<div class="sp-fp-notice mb-3" role="status">
    <span class="sp-stat-icon"><i class="fas fa-clock"></i></span>
    <div style="flex:1;min-width:0">
        <div class="sp-fp-notice-title">Log absensi ditarik otomatis setiap hari kerja (Senin–Jumat) pukul 09.00 WIB</div>
        <div class="sp-fp-notice-sub">
            Penarikan berikutnya: <strong>{{ $fpNext->translatedFormat('l, d F Y') }} pukul 09.00</strong>
            @if($fpLastAt)
                <span class="mx-1">·</span>
                Terakhir: {{ $fpLastAt->translatedFormat('d M Y, H.i') }}
                ({{ $fpLast['trigger'] === 'otomatis' ? 'otomatis' : 'manual' }})
                @if($fpLast['ok'])
                    <span class="sp-badge success ml-1">{{ $fpLast['inserted'] }} log baru</span>
                @else
                    <span class="sp-badge danger ml-1" title="{{ collect($fpLast['machines'])->where('ok', false)->pluck('error')->implode('; ') }}">Gagal</span>
                @endif
            @else
                <span class="mx-1">·</span> Belum pernah ditarik sejak fitur ini aktif.
            @endif
        </div>
        @if($fpLast && !$fpLast['ok'])
            <div class="sp-fp-notice-err">
                @foreach(collect($fpLast['machines'])->where('ok', false) as $m)
                    <div><i class="fas fa-exclamation-circle mr-1"></i>{{ $m['machine'] }}: {{ $m['error'] }}</div>
                @endforeach
            </div>
        @endif
    </div>
    @if($showButton ?? true)
        <a href="{{ route('wilayah.fingerprint-machine.get-log-attendance') }}" class="sp-btn" style="height:36px;font-size:13.5px;white-space:nowrap" data-sp-sync>
            <i class="fas fa-sync-alt"></i>Tarik sekarang
        </a>
    @endif
</div>

@once
    @push('js')
        <script>
            document.querySelectorAll('[data-sp-sync]').forEach(function (a) {
                a.addEventListener('click', function () {
                    a.style.pointerEvents = 'none';
                    a.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Menarik data…';
                });
            });
        </script>
    @endpush
@endonce
