@extends('adminlte::page')

@section('title', 'SIPEKAN | Manajemen Departemen Wilayah')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Wilayah {{$wilayah->nama}}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-cyan">
                <div class="card-header">
                    <h3 class="card-title">Tabel Department Expense</h3>
                </div>
                <div class="card-body table-responsive">
                    <a href="{{route('wilayah.department-expense.tambah', $wilayah->id)}}" class="btn btn-primary mb-2">
                        Tambah Department Expense
                    </a>
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Expense</th>
                            <th>Account Code</th>
                            <th>Opsi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($expense as $key => $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$item->nama}}</td>
                                <td>{{$item->account_code}}</td>
                                <td>
                                    <a href="{{route('wilayah.department-expense.edit', $item->id)}}" class="btn btn-warning btn-xs">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data</td>
                            </tr>
                        @endforelse
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
