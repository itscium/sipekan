@extends('adminlte::page')

@php
    $editing = $a->exists;
    $selected = array_map('intval', old('target_wilayah', $a->target_wilayah ?? []));
    $level = old('level', $a->level ?? 'penting');
    $display = old('display', $a->display ?? 'both');
@endphp

@section('title', 'SIPekan | ' . ($editing ? 'Ubah' : 'Tulis') . ' pengumuman')

@section('content_header')
    <h1>{{ $editing ? 'Ubah pengumuman' : 'Tulis pengumuman' }}</h1>
    <div class="sp-muted mt-1">Pengumuman tampil di Beranda pegawai pada Business Unit yang dipilih, selama masa tayangnya.</div>
@stop

@section('content')
<form method="post" action="{{ $editing ? route('admin.announcements.update', $a) : route('admin.announcements.store') }}" novalidate class="sp-ann-grid">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="sp-card">
        <div class="form-group">
            <label for="title">Judul</label>
            <input type="text" id="title" name="title" maxlength="200" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $a->title) }}" placeholder="SIPekan beralih ke {{ config('sipekan.portal.name') }}" data-pv="title">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="body">Isi pengumuman</label>
            <textarea id="body" name="body" rows="6" maxlength="3000" class="form-control @error('body') is-invalid @enderror" data-pv="body" placeholder="Mulai 1 Januari 2027, laporan keuangan dan slip gaji Anda tersedia di {{ config('sipekan.portal.name') }}.">{{ old('body', $a->body) }}</textarea>
            @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="button_text">Teks tombol</label>
                <input type="text" id="button_text" name="button_text" maxlength="100" class="form-control @error('button_text') is-invalid @enderror" value="{{ old('button_text', $a->button_text) }}" data-pv="btn">
                @error('button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-7">
                <label for="button_url">Tautan tombol</label>
                <input type="url" id="button_url" name="button_url" maxlength="500" class="form-control @error('button_url') is-invalid @enderror" value="{{ old('button_url', $a->button_url) }}" placeholder="{{ config('sipekan.portal.url') }}" data-pv="url">
                @error('button_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="level">Tingkat</label>
                <select id="level" name="level" class="custom-select" data-pv="level">
                    <option value="penting" @selected($level === 'penting')>Penting (kuning)</option>
                    <option value="info" @selected($level === 'info')>Informasi (biru)</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="display">Ditampilkan sebagai</label>
                <select id="display" name="display" class="custom-select">
                    <option value="both" @selected($display === 'both')>Pop-up saat membuka Beranda + banner</option>
                    <option value="banner" @selected($display === 'banner')>Banner saja</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="starts_at">Tayang mulai</label>
                <input type="date" id="starts_at" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" value="{{ old('starts_at', optional($a->starts_at)->format('Y-m-d')) }}">
                @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-6">
                <label for="ends_at">Tayang sampai <span class="sp-faint">(boleh kosong)</span></label>
                <input type="date" id="ends_at" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at', optional($a->ends_at)->format('Y-m-d')) }}">
                @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-group mb-2">
            <label class="d-block">Business Unit tujuan</label>
            <div class="d-flex mb-2" style="gap:8px">
                <button type="button" class="btn btn-default btn-sm" data-bu-all>Pilih semua</button>
                <button type="button" class="btn btn-default btn-sm" data-bu-none>Kosongkan</button>
            </div>
            <div class="sp-bu-list">
                @foreach($wilayah as $w)
                    <label class="sp-bu {{ in_array($w->id, $selected, true) ? 'on' : '' }}">
                        <input type="checkbox" name="target_wilayah[]" value="{{ $w->id }}" data-users="{{ $userCounts[$w->id] ?? 0 }}" @checked(in_array($w->id, $selected, true))>
                        <span class="sp-badge accent">{{ $w->short_code }}</span>
                        <span style="min-width:0">{{ $w->nama }}</span>
                        <span class="sp-faint sp-num ml-auto">{{ $userCounts[$w->id] ?? 0 }}</span>
                    </label>
                @endforeach
            </div>
            <div class="sp-faint mt-2" id="buSummary"></div>
            @error('target_wilayah')<div class="sp-field-error">{{ $message }}</div>@enderror
        </div>

        <label class="sp-check mt-3 mb-3">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $a->is_published))>
            Tayangkan sesuai jadwal (jika tidak dicentang, disimpan sebagai draf)
        </label>

        <div class="d-flex" style="gap:8px">
            <button type="submit" class="sp-btn sp-btn-primary"><i class="fas fa-check"></i>Simpan</button>
            <a href="{{ route('admin.announcements.index') }}" class="sp-btn">Batal</a>
        </div>
    </div>

    <div>
        <div class="sp-card" style="position:sticky;top:92px">
            <p class="sp-card-title mb-3">Pratinjau di Beranda pegawai</p>
            <div class="sp-ann" id="pvBox">
                <span class="sp-ann-ico" aria-hidden="true">!</span>
                <div style="min-width:0">
                    <div class="sp-ann-title" id="pvTitle"></div>
                    <div class="sp-ann-body" id="pvBody"></div>
                    <a class="sp-ann-cta" id="pvBtn" href="#" target="_blank" rel="noopener"></a>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@push('js')
<script>
(function () {
    var q = function (s) { return document.querySelector(s); };
    var boxes = document.querySelectorAll('input[name="target_wilayah[]"]');
    function summary() {
        var n = 0, users = 0;
        boxes.forEach(function (b) { b.closest('.sp-bu').classList.toggle('on', b.checked); if (b.checked) { n++; users += +b.dataset.users; } });
        q('#buSummary').textContent = n ? n + ' Business Unit dipilih · sekitar ' + users + ' pengguna aktif akan melihat pengumuman ini' : 'Belum ada Business Unit yang dipilih.';
    }
    function preview() {
        q('#pvTitle').textContent = q('[data-pv=title]').value || 'Judul pengumuman';
        q('#pvBody').textContent = q('[data-pv=body]').value || 'Isi pengumuman akan tampil di sini.';
        var btn = q('[data-pv=btn]').value, url = q('[data-pv=url]').value;
        q('#pvBtn').hidden = !btn;
        q('#pvBtn').textContent = btn + ' ↗';
        q('#pvBtn').href = /^https?:\/\//i.test(url) ? url : '#';
        q('#pvBox').className = 'sp-ann ' + q('[data-pv=level]').value;
        q('#pvBox .sp-ann-ico').textContent = q('[data-pv=level]').value === 'penting' ? '!' : 'i';
    }
    boxes.forEach(function (b) { b.addEventListener('change', summary); });
    q('[data-bu-all]').addEventListener('click', function () { boxes.forEach(function (b) { b.checked = true; }); summary(); });
    q('[data-bu-none]').addEventListener('click', function () { boxes.forEach(function (b) { b.checked = false; }); summary(); });
    document.querySelectorAll('[data-pv]').forEach(function (el) { el.addEventListener('input', preview); el.addEventListener('change', preview); });
    summary(); preview();
})();
</script>
@endpush
