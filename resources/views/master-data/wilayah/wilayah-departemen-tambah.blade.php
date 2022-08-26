@extends('adminlte::page')

@section('title', 'SIPEKAN | Tambah Departemen')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Input Departemen</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{route('wilayah.departemen.simpan')}}" method="POST">
                    @csrf
                    <input type="hidden" name="wilayah_id" value="{{$wilayah->id}}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_departemen">Nama Departemen</label>
                            <input type="text" class="form-control" id="nama_departemen" name="nama_departemen" placeholder="Masukkan nama Departemen">
                        </div>
                        <label for="kepala_departemen">Kepala Departemen</label>
                        <x-adminlte-select2 name="kepala_departemen" id="kepala_departemen">
                            <option selected disabled value="">Pilih Kepala Departemen</option>
                            @foreach($users as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </x-adminlte-select2>
                        <div class="form-group">
                            <label for="kode_akun_departemen">Kode Akun Departemen</label>
                            <input type="text" class="form-control" id="kode_akun_departemen" name="kode_akun_departemen" placeholder="Kode akun Departemen">
                        </div>
                        <div class="form-group">
                            <label for="kode_akun_travel">Kode Akun Travel</label>
                            <input type="text" class="form-control" id="kode_akun_travel" name="kode_akun_travel" placeholder="Kode akun Travel">
                        </div>
                        <div class="form-group">
                            <label for="kode_akun_special_travel">Kode Akun Special Travel</label>
                            <input type="text" class="form-control" id="kode_akun_special_travel" name="kode_akun_special_travel" placeholder="Kode akun Special Travel">
                        </div>
                        <div class="form-group">
                            <label for="kode_akun_strategic_plan">Kode Akun Strategic Plan</label>
                            <input type="text" class="form-control" id="kode_akun_strategic_plan" name="kode_akun_strategic_plan" placeholder="Kode akun Strategic Plan">
                        </div>
                        <div class="form-group">
                            <label for="kode_akun_office">Kode Akun Office</label>
                            <input type="text" class="form-control" id="kode_akun_office" name="kode_akun_office" placeholder="Kode akun Office">
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-right">
                        <a href="{{url()->previous()}}" class="btn btn-danger"><i class="fa fa-step-backward"></i> Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop
