@extends('adminlte::page')

@section('title', 'SIPEKAN | Tambah Pengguna')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Input User</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{route('wilayah.pengguna.simpan')}}" method="POST">
                    @csrf
                    <input type="hidden" name="wilayah_id" value="{{$wilayah->id}}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama Lengkap">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email">
                        </div>
                        <div class="form-group">
                            <label for="kode_akun_personal">Kode Akun Personal</label>
                            <input type="text" class="form-control" id="kode_akun_personal" name="kode_akun_personal" placeholder="Kode akun Personal">
                        </div>
                        <div class="form-group">
                            <label for="kode_akun_travel">Kode Akun Travel Personal</label>
                            <input type="text" class="form-control" id="kode_akun_travel" name="kode_akun_travel" placeholder="Kode akun Travel Personal">
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-right">
                        <a href="{{url()->previous()}}" class="btn btn-danger"><i class="fa fa-step-backward"></i> Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('js')

@endpush
