@extends('adminlte::page')

@section('title', 'SIPEKAN | Detail Keuangan Departemen')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Keuangan Departemen</h1>
@stop

@section('content')
    {{--    <livewire:personal-financial />--}}
    <div class="container-fluid">
        <div class="row">
            <!-- /.col -->
            <div class="col-md-9">
                <!-- /.card -->
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('departemen.keuangan.index') }}" class="btn btn-danger btn-xs float-right"><i class="fa fa-backward"></i> Back</a>
                        <h3 class="card-title">Detail Travel Expense</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0" style="height: 700px;">
                        <table class="table table-striped table-head-fixed">
                            <thead>
                            <tr>
                                <th>Period</th>
                                <th>Journal No</th>
                                <th>Reference</th>
                                <th>Description</th>
                                <th>Base Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($detail as $item)
                                <tr>
                                    <td>{{$item['period']}}</td>
                                    <td>{{$item['nomor_jurnal']}}</td>
                                    <td>{{$item['reference']}}</td>
                                    <td>{{$item['description']}}</td>
                                    <td class="text-right">{{$item['amount']}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-12">
                        <!-- /.card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">FILTER</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <form method="GET">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">PERIODE</label>
                                        <input type="month" name="periode" onchange="this.form.submit()" value="{{$periode}}" class="form-control" id="exampleInputEmail1">
                                        {{--                                        value="{{$tgl_akhir}}"--}}
                                    </div>
                                </form>

                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <!-- small card -->
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{$saldo}}</h3>

                                <p>Actual Expense</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.col -->
        </div>
    </div>
@stop

@push('js')<script>$(() => $("#drPlaceholder").val(''))</script>@endpush
