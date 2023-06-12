@extends('adminlte::page')

@section('title', 'SIPEKAN | Edit Wilayah')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"> Form Edit Wilayah</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{route('wilayah.update')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{$wilayah->id}}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="edit_nama">Nama Wilayah</label>
                            <input type="text" class="form-control" id="edit_nama" name="edit_nama" value="{{$wilayah->nama}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Kode Wilayah</label>
                            <input type="text" class="form-control" id="edit_email" name="edit_kode" value="{{$wilayah->kode}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_personal">Nomor Akun di WIUM</label>
                            <input type="text" class="form-control" id="edit_kode_akun_personal" name="edit_account_on_wium" value="{{$wilayah->account_on_wium}}">
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-right">
                        <a href="{{url()->previous()}}" class="btn btn-danger"><i class="fa fa-step-backward"></i> Cancel</a>
                        <button type="submit" class="btn btn-warning"><i class="fa fa-edit"></i> Update</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('js')

@endpush
