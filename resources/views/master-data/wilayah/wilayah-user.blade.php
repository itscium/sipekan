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
                    <div class="row">
                        <div class="col-md-8">
                            <a href="{{route('wilayah.pengguna.tambah', $wilayah->id)}}" class="btn btn-primary mb-2">
                                Tambah Users
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#user_salary_import">
                                Import User Salary
                            </button>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#user_import">
                                Import User
                            </button>
                        </div>
                    </div>
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Wilayah</th>
                            <th>Allowance</th>
                            <th class="text-center">Opsi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $key => $user)
                            <tr class="{{ $user->status === '0' ? 'bg-danger':'' }}">
                                <td>{{$key+1}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->wilayah->nama}}</td>
                                <td>{{ \App\Models\UserSalary::where('user_id', $user->id)->where('year', date('Y'))->count() }}</td>
                                <td class="text-center">
                                    <a href="{{route('wilayah.pengguna.edit', $user->id)}}" class="btn btn-primary btn-xs">
                                        Edit
                                    </a>
                                    <a href="{{route('impersonate', $user->id)}}" class="btn btn-primary btn-xs">
                                        <i class="fas fa-user-secret"></i>
                                    </a>
                                    @if($user->status === '0')
                                        <a href="{{route('users.aktifkan', $user->id)}}" class="btn btn-success btn-xs" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-check"></i> aktifkan
                                        </a>
                                    @else
                                        <a href="{{route('users.non-aktifkan', $user->id)}}" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-ban"></i> Non-aktifkan
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <form class="modal fade" id="user_salary_import" action="{{ route('wilayah.user.salary.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">User Salary Import</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="file" name="file">
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Import Data</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form class="modal fade" id="user_import" action="{{ route('wilayah.user.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">User Import</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="file" name="file">
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Import User</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
