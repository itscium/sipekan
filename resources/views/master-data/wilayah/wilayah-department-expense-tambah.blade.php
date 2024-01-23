@extends('adminlte::page')

@section('title', 'SIPEKAN | Tambah Department Expense')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Input Departmen Expense</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{route('wilayah.department-expense.simpan')}}" method="POST">
                    @csrf
                    <input type="hidden" name="wilayah_id" value="{{$wilayah->id}}">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_department_expense">Nama Department Expense</label>
                            <input type="text" class="form-control" id="nama_department_expense" name="nama_department_expense" placeholder="Masukkan nama Department Expense">
                        </div>
                        <div class="form-group">
                            <label for="account_code">Account Code</label>
                            <input type="text" class="form-control" id="account_code" name="account_code" placeholder="Kode Account Code">
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-right">
                        <a href="{{url()->previous()}}" class="btn btn-danger"><i class="fa fa-step-backward"></i> Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop
