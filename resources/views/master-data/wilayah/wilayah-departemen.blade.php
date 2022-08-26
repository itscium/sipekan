@extends('adminlte::page')

@section('title', 'Manajemen departemen')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Wilayah {{$wilayah->nama}}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-cyan">
                <div class="card-header">
                    <h3 class="card-title">Tabel Departemen</h3>
                </div>
                <div class="card-body table-responsive">
                    <a href="{{route('wilayah.departemen.tambah', $wilayah->id)}}" class="btn btn-primary mb-2">
                        Tambah Departemen
                    </a>
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                        <tr>
                            <th rowspan="2">No.</th>
                            <th rowspan="2">Nama Departemen</th>
                            <th rowspan="2">Kepala Departemen</th>
                            <th colspan="5" class="text-center">Account</th>
                            <th rowspan="2">Opsi</th>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <th>Travel</th>
                            <th>Special Travel</th>
                            <th>Strategic Plan</th>
                            <th>Office</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($departemens as $key => $departemen)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$departemen->nama_departemen}}</td>
                                <td>{{$departemen->user->name ?? ''}}</td>
                                <td>{{$departemen->department_code}}</td>
                                <td>{{$departemen->travel_expense_code}}</td>
                                <td>{{$departemen->travel_special_code}}</td>
                                <td>{{$departemen->strategic_plan_code}}</td>
                                <td>{{$departemen->office_expense_code}}</td>
                                <td>
                                    <a href="{{route('wilayah.departemen.edit', $departemen->id)}}" class="btn btn-primary btn-xs">
                                        Edit
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
