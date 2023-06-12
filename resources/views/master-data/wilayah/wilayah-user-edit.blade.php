@extends('adminlte::page')

@section('title', 'SIPEKAN | Edit Pengguna')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"> Form Edit User</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{route('wilayah.pengguna.update')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{$users->id}}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="edit_nama">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama" name="edit_nama" value="{{$users->name}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="edit_email" value="{{$users->email}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_personal">Kode Akun Personal</label>
                            <input type="text" class="form-control" id="edit_kode_akun_personal" name="edit_kode_akun_personal" value="{{$users->ACCNT_CODE}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_kode_akun_travel">Kode Akun Travel Personal</label>
                            <input type="text" class="form-control" id="edit_kode_akun_travel" name="edit_kode_akun_travel" value="{{$users->travel_account}}">
                        </div>
                        <div class="form-group">
                            <label for="">Pilih Roles</label>
                            <ul>
                                @foreach($role as $item)
                                    <li>
                                        <input type="checkbox" id="role" name="role[]" {{ in_array($item->id, $user_role) ? 'checked' : '' }} value="{{$item->id}}">
                                        <label for="role">{{$item->role}}</label>
                                    </li>
                                @endforeach
                            </ul>
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
