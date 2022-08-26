@extends('adminlte::page')

@section('title', 'Manajemen departemen')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Wilayah</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive">
{{--                    <a href="{{route('departemens.create')}}" class="btn btn-primary mb-2">--}}
{{--                        Tambah Departemen--}}
{{--                    </a>--}}
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Wilayah</th>
                            <th>Nama Wilayah</th>
                            <th class="text-center">Opsi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($wilayah as $key => $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item->kode}}</td>
                                <td>{{$item->nama}}</td>
                                <td class="text-center">
                                    <a href="{{route('wilayah.edit', $item->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i>
                                        Edit
                                    </a>
                                    <a href="{{route('wilayah.departemen', $item->id)}}" class="btn btn-primary btn-sm"><i class="fa fa-plus-square"></i>
                                        Departemen
                                    </a>
                                    <a href="{{route('wilayah.pengguna', $item->id)}}" class="btn btn-info btn-sm"><i class="fa fa-user-plus"></i>
                                        User
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
