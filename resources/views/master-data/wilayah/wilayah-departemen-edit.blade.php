@extends('adminlte::page')

@section('title', 'SIPEKAN | Edit Departemen')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Departemen</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{route('wilayah.departemen.update')}}" method="POST">
                    @csrf
                    <div class="card-body">
                        <input type="hidden" name="id" value="{{$departemen->id}}">
                        <div class="form-group">
                            <label for="edit_nama_departemen">Nama Departemen</label>
                            <input type="text" class="form-control" id="edit_nama_departemen" name="edit_nama_departemen" value="{{$departemen->nama_departemen}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_nama_departemen">Kepala Departemen</label>
                            <select name="edit_kepala_departemen" class="form-control" id="kepala_departemen">
                                <option selected disabled value="">Pilih Kepala Departemen</option>
                                @foreach($users as $item)
                                    <option value="{{$item->id}}" {{$departemen->kepala_departemen === $item->id ? 'Selected' : ''}}>{{$item->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_departemen">Kode Akun Departemen</label>
                            <input type="text" class="form-control" id="edit_kode_akun_departemen" name="edit_kode_akun_departemen" value="{{$departemen->department_code}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_travel">Kode Akun Travel</label>
                            <input type="text" class="form-control" id="edit_kode_akun_travel" name="edit_kode_akun_travel" value="{{$departemen->travel_expense_code}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_special_travel">Kode Akun Special Travel</label>
                            <input type="text" class="form-control" id="edit_kode_akun_special_travel" name="edit_kode_akun_special_travel" value="{{$departemen->travel_special_code}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_strategic_plan">Kode Akun Strategic Plan</label>
                            <input type="text" class="form-control" id="edit_kode_akun_strategic_plan" name="edit_kode_akun_strategic_plan" value="{{$departemen->strategic_plan_code}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_office">Kode Akun Office</label>
                            <input type="text" class="form-control" id="edit_kode_akun_office" name="edit_kode_akun_office" value="{{$departemen->office_expense_code}}">
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-right">
                        <a href="{{url()->previous()}}" class="btn btn-danger"><i class="fa fa-step-backward"></i> Cancel</a>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('js')

@endpush
