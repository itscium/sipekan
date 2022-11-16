@extends('adminlte::page')

@section('title', 'SIPEKAN | Manajemen Departemen Wilayah')

@section('content_header')
    <h1 class="m-0 text-dark">Report Departemen</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-cyan">
                <div class="card-header">
                    <h3 class="card-title">Tabel Departemen</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Departemen</th>
                                <th>Kepala Departemen</th>
                                <th>Kode Departemen</th>
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($departemens as $key => $departemen)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$departemen->nama_departemen}}</td>
                                <td>{{$departemen->user->name ?? ''}}</td>
                                <td>{{$departemen->department_code}}</td>
                                <td>
                                    <a href="{{route('report.departemen.show', $departemen->id)}}" class="btn btn-dark btn-xs"><i class="fas fa-eye"></i>
                                        lihat
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
