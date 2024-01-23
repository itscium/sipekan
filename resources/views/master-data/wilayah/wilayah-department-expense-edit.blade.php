@extends('adminlte::page')

@section('title', 'SIPEKAN | Edit Department Expense')

@section('content_header')
    <h1 class="m-0 text-dark"></h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Department Expense</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{route('wilayah.department-expense.update')}}" method="POST">
                    @csrf
                    <div class="card-body">
                        <input type="hidden" name="id" value="{{$department->id}}">
                        <div class="form-group">
                            <label for="edit_nama">Nama Department Expense</label>
                            <input type="text" class="form-control" id="edit_nama" name="edit_nama" value="{{$department->nama}}">
                        </div>
                        <div class="form-group">
                            <label for="edit_account_code">Account Code</label>
                            <input type="text" class="form-control" id="edit_account_code" name="edit_account_code" value="{{$department->account_code}}">
                        </div>
                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer text-right">
                        <a href="{{url()->previous()}}" class="btn btn-danger"><i class="fa fa-step-backward"></i> Cancel</a>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>
@stop

@push('js')

@endpush
