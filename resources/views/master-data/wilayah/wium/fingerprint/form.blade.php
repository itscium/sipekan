@extends('adminlte::page')

@php($editing = $machine->exists)

@section('title', 'SIPekan | ' . ($editing ? 'Ubah' : 'Tambah') . ' mesin fingerprint')

@section('content_header')
    <h1>{{ $editing ? 'Ubah mesin fingerprint' : 'Tambah mesin fingerprint' }}</h1>
    <div class="sp-muted mt-1">
        @if($editing)
            Saat ini terhubung ke <code>{{ $machine->getOriginal('ip_address') }}:{{ $machine->getOriginal('port') }}</code>
        @else
            Daftarkan mesin baru agar user dan log absensinya bisa ditarik.
        @endif
    </div>
@stop

@section('content')
    <div class="sp-card" style="max-width:640px">
        <form method="post"
              action="{{ $editing ? route('wilayah.fingerprint-machine.update', [$id_wilayah, $machine->id]) : route('wilayah.fingerprint-machine.store', $id_wilayah) }}"
              novalidate>
            @csrf
            @if($editing) @method('PUT') @endif

            <div class="form-group">
                <label for="location">Lokasi</label>
                <input type="text" id="location" name="location" maxlength="100"
                       class="form-control @error('location') is-invalid @enderror"
                       value="{{ old('location', $machine->location) }}" placeholder="Lantai 3" required>
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="form-group col-sm-8">
                    <label for="ip_address">IP address</label>
                    <input type="text" id="ip_address" name="ip_address" inputmode="decimal"
                           class="form-control @error('ip_address') is-invalid @enderror"
                           value="{{ old('ip_address', $machine->ip_address) }}" placeholder="10.5.0.200" required>
                    @error('ip_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-sm-4">
                    <label for="port">Port</label>
                    <input type="number" id="port" name="port" min="1" max="65535"
                           class="form-control @error('port') is-invalid @enderror"
                           value="{{ old('port', $machine->port) }}" placeholder="80" required>
                    @error('port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <p class="sp-muted mb-4" style="font-size:13px">
                <i class="fas fa-info-circle mr-1" style="color:var(--sp-accent)"></i>
                Web service mesin yang dipakai SIPekan biasanya di port <strong>80</strong>. Port 4370 adalah protokol bawaan ZKTeco.
            </p>

            <div class="d-flex" style="gap:8px">
                <button type="submit" class="sp-btn sp-btn-primary"><i class="fas fa-check"></i>{{ $editing ? 'Simpan perubahan' : 'Tambah mesin' }}</button>
                <a href="{{ route('wilayah.fingerprint-machine', $id_wilayah) }}" class="sp-btn">Batal</a>
            </div>
        </form>
    </div>
@stop
