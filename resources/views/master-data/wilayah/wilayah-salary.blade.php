@extends('adminlte::page')

@section('title', 'SIPEKAN | Manajemen Pengguna Wilayah')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Salary & Allowances Wilayah {{$wilayah->nama}}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-cyan">
                <div class="card-header">
                    <h3 class="card-title">Tabel Salary & Allowances</h3>
                </div>
                <div class="card-body table-responsive">
                    <div class="row">
                        <div class="col-md-8">
                            <a href="{{route('wilayah.salary.tambah', $wilayah->id)}}" class="btn btn-primary mb-2">
                                Tambah Salary & Allowances
                            </a>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('wilayah.salary.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <div class="custom-file text-left">
                                        <input type="file" name="file" class="custom-file-input" id="customFile">
                                        <label class="custom-file-label" for="customFile">Choose file</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary text-right">Import data</button>
                            </form>
                        </div>
                    </div>
                    <table class="table table-hover table-bordered table-stripped" id="example2">
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th class="text-center">Opsi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($salary as $key => $item)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{ucfirst($item->nama)}}</td>
                                <td>{{ucfirst($item->tipe)}}</td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-warning btn-xs">
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
