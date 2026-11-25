@extends('adminlte::page')

@php
    $editing = $u->exists;
    $selected = array_map('intval', old('wilayah', $editing ? ($u->managedWilayahIds() ?? []) : []));
@endphp

@section('title', 'SIPekan | ' . ($editing ? 'Ubah' : 'Tambah') . ' admin wilayah')

@section('content_header')
    <h1>{{ $editing ? 'Ubah admin wilayah' : 'Tambah admin wilayah' }}</h1>
    <div class="sp-muted mt-1">Admin ini hanya bisa melihat dan mengelola Business Unit yang dicentang.</div>
@stop

@section('content')
<form method="post" action="{{ $editing ? route('admin.regional-admins.update', $u->id) : route('admin.regional-admins.store') }}" novalidate autocomplete="off" style="max-width:760px">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="sp-card">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="name">Nama</label>
                <input type="text" id="name" name="name" maxlength="255" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $u->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email (untuk masuk)</label>
                <input type="email" id="email" name="email" maxlength="255" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $u->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="password">Kata sandi @if($editing)<span class="sp-faint">(kosongkan jika tidak diganti)</span>@endif</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="sp-faint">Minimal 8 karakter.</small>
            </div>
            <div class="form-group col-md-6">
                <label for="password_confirmation">Ulangi kata sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
            </div>
        </div>

        <div class="form-group mb-2">
            <label class="d-block">Business Unit yang dikelola</label>
            <div class="sp-bu-list">
                @foreach($wilayah as $w)
                    <label class="sp-bu {{ in_array($w->id, $selected, true) ? 'on' : '' }}">
                        <input type="checkbox" name="wilayah[]" value="{{ $w->id }}" @checked(in_array($w->id, $selected, true))>
                        <span class="sp-badge accent">{{ $w->short_code }}</span>
                        <span style="min-width:0">{{ $w->nama }}</span>
                    </label>
                @endforeach
            </div>
            <div class="sp-faint mt-2" id="buSummary"></div>
            @error('wilayah')<div class="sp-field-error">{{ $message }}</div>@enderror
            @error('wilayah.*')<div class="sp-field-error">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex mt-3" style="gap:8px">
            <button type="submit" class="sp-btn sp-btn-primary"><i class="fas fa-check"></i>Simpan admin wilayah</button>
            <a href="{{ route('admin.regional-admins.index') }}" class="sp-btn">Batal</a>
        </div>
    </div>
</form>
@stop

@push('js')
<script>
(function () {
    var boxes = document.querySelectorAll('input[name="wilayah[]"]');
    function summary() {
        var n = 0;
        boxes.forEach(function (b) { b.closest('.sp-bu').classList.toggle('on', b.checked); if (b.checked) n++; });
        document.getElementById('buSummary').textContent = n ? n + ' Business Unit dipilih' : 'Belum ada Business Unit yang dipilih.';
    }
    boxes.forEach(function (b) { b.addEventListener('change', summary); });
    summary();
})();
</script>
@endpush
