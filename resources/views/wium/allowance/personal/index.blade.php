@extends('adminlte::page')

@section('title', 'SIPEKAN | Manajemen Pengguna Wilayah')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Employee Allowance</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-cyan">
                <div class="card-header">
                    <h3 class="card-title">Tabel User</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>ER</th>
                            <th>Allowance</th>
                            <th class="text-center">Opsi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $key => $user)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->ACCNT_CODE}}</td>
                                <td>{{$user->allowance->count()}}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-dark btn-xs"><i class="fa fa-eye"></i>
                                        Show
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
