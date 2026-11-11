@extends('adminlte::page')

@section('title', 'SIPekan | Mesin fingerprint')

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
        <div>
            <h1>Mesin fingerprint</h1>
            <div class="sp-muted mt-1">Atur IP address dan port mesin absensi. Jika mesin diganti, cukup ubah datanya di sini.</div>
        </div>
        <div class="d-flex" style="gap:8px">
            <a href="{{ route('wilayah.index') }}" class="sp-btn" style="height:38px"><i class="fas fa-arrow-left"></i>Kembali</a>
            <a href="{{ route('wilayah.fingerprint-machine.create', $id_wilayah) }}" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-plus"></i>Tambah mesin</a>
        </div>
    </div>
@stop

@section('content')
    @include('partials.sp-fp-schedule')

    @if($list_machine->isEmpty())
        <div class="sp-card text-center py-5">
            <i class="fas fa-fingerprint fa-2x sp-faint mb-3"></i>
            <p class="sp-card-title">Belum ada mesin terdaftar</p>
            <p class="sp-card-sub mb-3">Tambahkan mesin fingerprint agar data user dan log absensi bisa ditarik.</p>
            <a href="{{ route('wilayah.fingerprint-machine.create', $id_wilayah) }}" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-plus"></i>Tambah mesin</a>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th style="width:56px">No.</th>
                        <th>Lokasi</th>
                        <th>IP address</th>
                        <th>Port</th>
                        <th class="text-right">User</th>
                        <th>Log terakhir</th>
                        <th class="text-right">Opsi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($list_machine as $key => $item)
                        @php($last = isset($last_log[$item->id]) ? \Illuminate\Support\Carbon::parse($last_log[$item->id]) : null)
                        <tr>
                            <td class="sp-muted">{{ $key + 1 }}</td>
                            <td><strong style="font-weight:500">{{ $item->location }}</strong></td>
                            <td><code style="color:var(--sp-text);background:var(--sp-surface-2);padding:2px 6px;border-radius:6px">{{ $item->ip_address }}</code></td>
                            <td class="sp-num">{{ $item->port }}</td>
                            <td class="text-right sp-num">{{ $item->users_count }}</td>
                            <td>
                                @if($last)
                                    {{ $last->format('d M Y') }}
                                    @if($last->lt(now()->subDays(3)))
                                        <span class="sp-badge warning ml-1" title="Log tidak bertambah lebih dari 3 hari">Tertunda</span>
                                    @endif
                                @else
                                    <span class="sp-faint">Belum ada</span>
                                @endif
                            </td>
                            <td class="text-right" style="white-space:nowrap">
                                <form action="{{ route('wilayah.fingerprint-machine.test', [$id_wilayah, $item->id]) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" data-sp-loading="Menghubungi…"><i class="fas fa-plug"></i> Tes koneksi</button>
                                </form>
                                {{-- Tetap wilayah 1 (Uni) seperti sebelumnya: daftar user mesin dihubungkan ke pegawai Uni --}}
                                <a href="{{ route('wilayah.fingerprint-machine.list-user', ['id_wilayah' => 1, 'id_machine' => $item->id]) }}" class="btn btn-info btn-sm"><i class="fa fa-users"></i> User</a>
                                <a href="{{ route('wilayah.fingerprint-machine.log-attendance', ['id_wilayah' => 1, 'id_machine' => $item->id]) }}" class="btn btn-info btn-sm"><i class="fa fa-fingerprint"></i> Log</a>
                                <a href="{{ route('wilayah.fingerprint-machine.edit', [$id_wilayah, $item->id]) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Ubah</a>
                                <form action="{{ route('wilayah.fingerprint-machine.destroy', [$id_wilayah, $item->id]) }}" method="post" class="d-inline"
                                      onsubmit="return confirm('Hapus mesin {{ $item->location }} ({{ $item->ip_address }}:{{ $item->port }})?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus" aria-label="Hapus"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sp-card" style="background:var(--sp-surface-2)">
            <div class="d-flex" style="gap:12px">
                <i class="fas fa-info-circle mt-1" style="color:var(--sp-accent)"></i>
                <div style="font-size:13.5px" class="sp-muted">
                    SIPekan menarik data lewat <strong>web service</strong> mesin (biasanya <strong>port 80</strong>).
                    Port <strong>4370</strong> di aplikasi bawaan mesin adalah protokol ZKTeco yang berbeda.
                    Setelah mengubah IP atau port, klik <strong>Tes koneksi</strong> untuk memastikan server bisa menjangkau mesin.
                </div>
            </div>
        </div>
    @endif
@stop

@push('js')
    <script>
        document.querySelectorAll('[data-sp-loading]').forEach(function (b) {
            b.form.addEventListener('submit', function () {
                b.disabled = true;
                b.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + b.dataset.spLoading;
            });
        });
    </script>
@endpush
