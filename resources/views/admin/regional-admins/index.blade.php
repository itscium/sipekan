@extends('adminlte::page')

@section('title', 'SIPekan | Admin wilayah')

@section('content_header')
    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:12px">
        <div>
            <h1>Admin wilayah</h1>
            <div class="sp-muted mt-1" style="max-width:70ch">Admin yang hanya bisa melihat dan mengelola Business Unit tertentu: pengguna, departemen, tunjangan departemen, log aktivitas, pengumuman, dan akses keuangan. Admin wilayah tidak bisa membuka menu ini, SOP, impersonate, atau mengubah data wilayah.</div>
        </div>
        <a href="{{ route('admin.regional-admins.create') }}" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-plus"></i>Tambah admin wilayah</a>
    </div>
@stop

@section('content')
    @if($admins->isEmpty())
        <div class="sp-card text-center py-5">
            <i class="fas fa-user-shield fa-2x sp-faint mb-3"></i>
            <p class="sp-card-title">Belum ada admin wilayah</p>
            <p class="sp-card-sub mb-3">Tambahkan admin yang hanya mengelola beberapa Business Unit.</p>
            <a href="{{ route('admin.regional-admins.create') }}" class="sp-btn sp-btn-primary" style="height:38px"><i class="fas fa-plus"></i>Tambah admin wilayah</a>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr><th>Nama</th><th>Email</th><th>Business Unit yang dikelola</th><th class="text-right">Opsi</th></tr>
                    </thead>
                    <tbody>
                    @foreach($admins as $u)
                        <tr>
                            <td style="min-width:180px"><strong style="font-weight:600">{{ $u->name }}</strong></td>
                            <td>{{ $u->email }}</td>
                            <td style="max-width:320px">
                                @foreach($u->managedWilayahIds() as $id)
                                    @if($wilayah->has($id))<span class="sp-badge accent mb-1" title="{{ $wilayah[$id]->nama }}">{{ $wilayah[$id]->short_code }}</span>@endif
                                @endforeach
                            </td>
                            <td class="text-right" style="white-space:nowrap">
                                <a href="{{ route('admin.regional-admins.edit', $u->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Ubah</a>
                                <form action="{{ route('admin.regional-admins.destroy', $u->id) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus akun admin wilayah ini? Akun ini tidak bisa masuk lagi.')">@csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" aria-label="Hapus {{ $u->name }}"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@stop
