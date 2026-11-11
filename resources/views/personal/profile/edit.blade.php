@extends('adminlte::page')

@section('title', 'SIPekan | Edit profil')

@php($tgl = old('tanggal_lahir', $user->tanggal_lahir ? \Illuminate\Support\Carbon::parse($user->tanggal_lahir)->format('Y-m-d') : ''))

@section('content_header')
    <h1>Edit profil</h1>
    <div class="sp-muted mt-1">Perubahan langsung terlihat di halaman profil Anda.</div>
@stop

@section('content')
    <form method="post" action="{{ route('personal.profile.update') }}" enctype="multipart/form-data" novalidate class="sp-profile">
        @csrf
        @method('PUT')

        {{-- Foto --}}
        <div>
            <div class="sp-card text-center" id="foto">
                <p class="sp-card-title mb-3 text-left">Foto profil</p>
                <div class="sp-profile-photo">
                    <img id="spPhotoPreview" src="{{ $user->foto_url ?? '' }}" alt="Pratinjau foto"
                         class="sp-avatar sp-avatar-img" style="width:120px;height:120px;{{ $user->foto_url ? '' : 'display:none' }}">
                    <span id="spPhotoInitials" class="sp-avatar"
                          style="width:120px;height:120px;font-size:40px;{{ $user->foto_url ? 'display:none' : '' }}">{{ $user->initials }}</span>
                </div>

                <label class="sp-btn mt-3 mb-2" style="height:36px;font-size:13.5px;cursor:pointer">
                    <i class="fas fa-upload"></i>Pilih foto
                    <input type="file" name="foto" id="spPhotoInput" accept="image/jpeg,image/png,image/webp" hidden>
                </label>
                <input type="hidden" name="hapus_foto" id="spPhotoRemove" value="0">
                <div>
                    <button type="button" id="spPhotoRemoveBtn" class="btn btn-link btn-sm text-danger p-0"
                            style="{{ $user->foto_url ? '' : 'display:none' }}">Hapus foto</button>
                </div>
                <p class="sp-faint mt-2 mb-0" style="font-size:12.5px">JPG, PNG, atau WEBP, maksimal 3 MB. Foto dipotong persegi secara otomatis.</p>
                @error('foto')<div class="sp-field-error">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Data --}}
        <div class="sp-card">
            <p class="sp-card-title mb-3">Data pribadi</p>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nama lengkap</label>
                    <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                </div>
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="text" class="form-control" value="{{ $user->email }}" disabled>
                </div>
            </div>
            <p class="sp-faint" style="font-size:12.5px;margin-top:-6px"><i class="fas fa-lock mr-1"></i>Nama dan email diatur oleh admin.</p>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="no_hp">No. HP</label>
                    <input type="tel" id="no_hp" name="no_hp" maxlength="30" class="form-control @error('no_hp') is-invalid @enderror"
                           value="{{ old('no_hp', $user->no_hp) }}" placeholder="0812 3456 7890">
                    @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="jenis_kelamin">Jenis kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="custom-select @error('jenis_kelamin') is-invalid @enderror">
                        <option value="">Pilih</option>
                        @foreach(['Laki-laki', 'Perempuan'] as $jk)
                            <option value="{{ $jk }}" {{ old('jenis_kelamin', $user->jenis_kelamin) === $jk ? 'selected' : '' }}>{{ $jk }}</option>
                        @endforeach
                    </select>
                    @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="tempat_lahir">Tempat lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" maxlength="100" class="form-control @error('tempat_lahir') is-invalid @enderror"
                           value="{{ old('tempat_lahir', $user->tempat_lahir) }}" placeholder="Manado">
                    @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="tanggal_lahir">Tanggal lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" max="{{ now()->subDay()->format('Y-m-d') }}"
                           class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ $tgl }}">
                    @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" maxlength="255" class="form-control @error('alamat') is-invalid @enderror"
                          placeholder="Jl. M.T. Haryono Kav. 4–5, Jakarta Selatan">{{ old('alamat', $user->alamat) }}</textarea>
                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <hr style="border-color:var(--sp-border);margin:20px 0">
            <p class="sp-card-title mb-3">Tentang saya</p>

            <div class="form-group">
                <label for="pendidikan">Pendidikan</label>
                <input type="text" id="pendidikan" name="pendidikan" maxlength="255" class="form-control @error('pendidikan') is-invalid @enderror"
                       value="{{ old('pendidikan', $user->pendidikan) }}" placeholder="S1 Akuntansi, Universitas Advent Indonesia">
                @error('pendidikan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="keahlian">Keahlian</label>
                <input type="text" id="keahlian" name="keahlian" maxlength="255" class="form-control @error('keahlian') is-invalid @enderror"
                       value="{{ old('keahlian', $user->keahlian) }}" placeholder="Akuntansi, Perpajakan, Excel">
                <small class="sp-faint">Pisahkan dengan koma.</small>
                @error('keahlian')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="tentang">Catatan</label>
                <textarea id="tentang" name="tentang" rows="4" maxlength="1000" class="form-control @error('tentang') is-invalid @enderror"
                          placeholder="Ceritakan sedikit tentang tugas atau pelayanan Anda.">{{ old('tentang', $user->tentang) }}</textarea>
                <small class="sp-faint"><span id="spTentangCount">{{ mb_strlen(old('tentang', $user->tentang ?? '')) }}</span>/1000</small>
                @error('tentang')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex mt-4" style="gap:8px">
                <button type="submit" class="sp-btn sp-btn-primary"><i class="fas fa-check"></i>Simpan profil</button>
                <a href="{{ route('personal.profile.index') }}" class="sp-btn">Batal</a>
            </div>
        </div>
    </form>
@stop

@push('js')
    <script>
        (function () {
            var input = document.getElementById('spPhotoInput');
            var preview = document.getElementById('spPhotoPreview');
            var initials = document.getElementById('spPhotoInitials');
            var removeFlag = document.getElementById('spPhotoRemove');
            var removeBtn = document.getElementById('spPhotoRemoveBtn');

            input.addEventListener('change', function () {
                var f = input.files[0];
                if (!f) return;
                if (f.size > 3 * 1024 * 1024) {
                    alert('Ukuran foto maksimal 3 MB.');
                    input.value = '';
                    return;
                }
                preview.src = URL.createObjectURL(f);
                preview.style.display = '';
                initials.style.display = 'none';
                removeFlag.value = '0';
                removeBtn.style.display = '';
            });

            removeBtn.addEventListener('click', function () {
                input.value = '';
                removeFlag.value = '1';
                preview.style.display = 'none';
                initials.style.display = '';
                removeBtn.style.display = 'none';
            });

            var t = document.getElementById('tentang');
            var c = document.getElementById('spTentangCount');
            t.addEventListener('input', function () { c.textContent = t.value.length; });
        })();
    </script>
@endpush
