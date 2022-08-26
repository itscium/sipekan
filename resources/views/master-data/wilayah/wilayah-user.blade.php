@extends('adminlte::page')

@section('title', 'SIPEKAN | Manajemen Pengguna Wilayah')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Users Wilayah {{$wilayah->nama}}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-cyan">
                <div class="card-header">
                    <h3 class="card-title">Tabel User</h3>
                </div>
                <div class="card-body table-responsive">
                    <a href="{{route('wilayah.pengguna.tambah', $wilayah->id)}}" class="btn btn-primary mb-2">
                        Tambah Users
                    </a>
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Wilayah</th>
                            <th class="text-center">Opsi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $key => $user)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->wilayah->nama}}</td>
                                <td class="text-center">
                                    <a href="{{route('wilayah.pengguna.edit', $user->id)}}" class="btn btn-primary btn-xs">
                                        Edit
                                    </a>
                                    <a href="{{route('impersonate', $user->id)}}" class="btn btn-primary btn-xs">
                                        <i class="fas fa-user-secret"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <form action="" id="delete-form" method="post">
        @method('delete')
        @csrf
    </form>
    <script>
        let msg = '{{Session::get('alert')}}';
        let exist = '{{Session::has('alert')}}';
        if(exist){
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: msg,
                showConfirmButton: false,
                timer: 1500
            })
        }
    </script>
    <script>
        $('#example2').DataTable({
            "responsive": true,
        });
        function notificationBeforeDelete(event, el) {
            event.preventDefault();
            if (confirm('Apakah anda yakin akan menghapus data ? ')) {
                $("#delete-form").attr('action', $(el).attr('href'));
                $("#delete-form").submit();
            }
        }
    </script>
@endpush
