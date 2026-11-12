{{--
    Panel "Sedang online" + notifikasi pengguna baru online (khusus admin).
    Log aktivitas lengkap ada di halaman tersendiri: menu Admin > Log aktivitas.
--}}
<div class="sp-grid mb-3" id="spActivity" data-feed="{{ route('admin.activity.feed') }}">
    <div class="sp-card">
        <div class="sp-card-head">
            <div>
                <p class="sp-card-title">Sedang online</p>
                <p class="sp-card-sub">Aktif dalam {{ \App\Services\ActivityTracker::ONLINE_MINUTES }} menit terakhir</p>
            </div>
            <span class="sp-badge success" id="spOnlineCount">● –</span>
        </div>
        <ul class="sp-list" id="spOnlineList"><li class="sp-faint" style="font-size:13px">Memuat…</li></ul>
        <div class="sp-activity-foot">
            <span>Hari ini: <strong id="spTodayUsers">–</strong> pengguna aktif · diperbarui <span id="spFeedTime">–</span></span>
            <span id="spFailedToday"></span>
            @unless(request()->routeIs('admin.activity.*'))
                <a href="{{ route('admin.activity.index') }}" class="small">Lihat log aktivitas</a>
            @endunless
        </div>
    </div>
</div>

<div class="sp-toasts" id="spToasts" aria-live="polite"></div>

@push('js')
<script>
(function () {
    var root = document.getElementById('spActivity');
    if (!root) return;
    var feedUrl = root.dataset.feed;
    var known = null; // id pengguna online pada pembaruan sebelumnya

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function avatar(u, size) {
        var dot = '<span class="sp-online-dot"></span>';
        if (u.photo) {
            return '<span class="sp-online-av" style="width:' + size + 'px;height:' + size + 'px"><img src="' + esc(u.photo) + '" alt="" class="sp-avatar sp-avatar-img" style="width:' + size + 'px;height:' + size + 'px">' + dot + '</span>';
        }
        return '<span class="sp-online-av" style="width:' + size + 'px;height:' + size + 'px"><span class="sp-avatar" style="width:' + size + 'px;height:' + size + 'px;font-size:11px">' + esc(u.initials) + '</span>' + dot + '</span>';
    }

    function toast(u) {
        var box = document.getElementById('spToasts');
        var el = document.createElement('div');
        el.className = 'sp-toast';
        el.innerHTML = avatar(u, 32) +
            '<div style="flex:1;min-width:0"><div style="font-weight:600">' + esc(u.name) + ' baru online</div>' +
            '<div class="sp-faint" style="font-size:12px">' + esc(u.wilayah || '') + ' · ' + esc(u.label) + '</div></div>' +
            '<button type="button" class="sp-toast-x" aria-label="Tutup">&times;</button>';
        el.querySelector('button').onclick = function () { el.remove(); };
        box.appendChild(el);
        setTimeout(function () { el.classList.add('hide'); setTimeout(function () { el.remove(); }, 400); }, 6000);
    }

    function render(d) {
        document.getElementById('spOnlineCount').textContent = '● ' + d.online.length + ' pengguna';
        document.getElementById('spTodayUsers').textContent = d.today_users;
        document.getElementById('spFeedTime').textContent = 'pukul ' + d.server_time;
        document.getElementById('spFailedToday').innerHTML = d.failed_today > 0
            ? '<span class="sp-badge danger">' + d.failed_today + ' gagal masuk hari ini</span>' : '';

        document.getElementById('spOnlineList').innerHTML = d.online.length ? d.online.map(function (u) {
            return '<li>' + avatar(u, 34) + '<div class="sp-list-main"><div class="sp-list-title">' + esc(u.name) + '</div>' +
                '<div class="sp-list-sub">' + esc(u.label) + ' · ' + esc(u.ago) + (u.wilayah ? ' · ' + esc(u.wilayah) : '') + '</div></div></li>';
        }).join('') : '<li class="sp-faint" style="font-size:13px">Tidak ada pengguna lain yang online.</li>';


        // Notifikasi untuk pengguna yang baru muncul (bukan saat pertama kali dimuat)
        var ids = d.online.map(function (u) { return u.id; });
        if (known !== null) {
            d.online.forEach(function (u) { if (known.indexOf(u.id) === -1) toast(u); });
        }
        known = ids;
    }

    function load() {
        if (document.hidden) return;
        fetch(feedUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw r; return r.json(); })
            .then(render)
            .catch(function () { document.getElementById('spFeedTime').textContent = 'gagal memuat, mencoba lagi…'; });
    }

    load();
    setInterval(load, 30000);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) load(); });
})();
</script>
@endpush
