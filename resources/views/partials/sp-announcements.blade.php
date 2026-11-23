{{--
    Pengumuman untuk pegawai di Beranda: banner untuk semua pengumuman yang sedang tayang,
    dan pop-up sekali per sesi untuk pengumuman bertipe "Pop-up + banner".
    Butuh: $announcements (koleksi dari Announcement::activeFor)
--}}
@if($announcements->isNotEmpty())
    <div class="d-flex flex-column mb-3" style="gap:12px">
        @foreach($announcements as $a)
            <div class="sp-ann {{ $a->level }}" role="region" aria-label="Pengumuman">
                <span class="sp-ann-ico" aria-hidden="true">{{ $a->level === 'penting' ? '!' : 'i' }}</span>
                <div style="min-width:0">
                    <div class="sp-ann-title">{{ $a->title }}</div>
                    <div class="sp-ann-body">{{ $a->body }}</div>
                    @if($a->button_text && $a->button_url)
                        <a class="sp-ann-cta" href="{{ $a->button_url }}" target="_blank" rel="noopener">{{ $a->button_text }} <i class="fas fa-external-link-alt" style="font-size:11px"></i></a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @php
        // Pop-up hanya untuk pengumuman pertama bertipe pop-up yang belum dilihat di sesi ini
        $popup = $announcements->first(fn ($a) => $a->display === 'both' && !in_array($a->id, session('ann_seen', []), true));
        if ($popup) { session()->push('ann_seen', $popup->id); }
    @endphp
    @if($popup)
        <div class="sp-ann-modal-back" id="spAnnModal" role="dialog" aria-modal="true" aria-labelledby="spAnnModalTitle">
            <div class="sp-ann-modal">
                <div class="sp-ann {{ $popup->level }}" style="border:0;background:transparent;padding:0">
                    <span class="sp-ann-ico" aria-hidden="true">{{ $popup->level === 'penting' ? '!' : 'i' }}</span>
                    <div style="min-width:0">
                        <div class="sp-ann-title" id="spAnnModalTitle" style="font-size:18px">{{ $popup->title }}</div>
                        <div class="sp-ann-body">{{ $popup->body }}</div>
                    </div>
                </div>
                <div class="d-flex justify-content-end flex-wrap mt-4" style="gap:8px">
                    <button type="button" class="sp-btn" data-ann-close>Nanti saja</button>
                    @if($popup->button_text && $popup->button_url)
                        <a href="{{ $popup->button_url }}" target="_blank" rel="noopener" class="sp-btn sp-btn-primary">{{ $popup->button_text }} <i class="fas fa-external-link-alt" style="font-size:12px"></i></a>
                    @endif
                </div>
            </div>
        </div>
        @push('js')
            <script>
                (function () {
                    var m = document.getElementById('spAnnModal');
                    if (!m) return;
                    var close = function () { m.remove(); };
                    m.querySelector('[data-ann-close]').addEventListener('click', close);
                    m.addEventListener('click', function (e) { if (e.target === m) close(); });
                    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
                    var focusable = m.querySelector('.sp-btn-primary') || m.querySelector('[data-ann-close]');
                    if (focusable) focusable.focus();
                })();
            </script>
        @endpush
    @endif
@endif
