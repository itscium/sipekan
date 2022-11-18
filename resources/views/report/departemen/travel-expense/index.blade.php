@extends('adminlte::page')

@section('title', 'SIPEKAN | Detail Keuangan Departemen')

@section('content_header')
    <h1 class="m-0 text-dark">Department Expense Report - Travel Expense</h1>
@stop

@section('content')
    {{--    <livewire:personal-financial />--}}
    <div class="container-fluid">
        <div class="row">
            <!-- /.col -->
            <div class="col-md-9">
                <!-- /.card -->
                <div class="card-primary">
{{--                    <div class="card-header">--}}
{{--                        <h3 class="card-title">Detail Travel Expense</h3>--}}
{{--                    </div>--}}
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0" style="height: 700px;">
                        <table class="table table-sm table-striped table-head-fixed">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Departmen Name</th>
                                <th>Budget</th>
                                <th>Actual</th>
                                <th>Cash/Travel Advance</th>
                                <th>Variance</th>
                                <th>Details</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data as $index=>$item)
                                <tr>
                                    <td>{{$index+1}}</td>
                                    <td>{{$item['nama_departemen']}}</td>
                                    <td>{{$item['budget']}}</td>
                                    <td>{{$item['actual']}}</td>
                                    <td>{{$item['advance']}}</td>
                                    <td class="{{$item['sisa'] < 0 ? 'text-danger font-weight-bold' : ''}}">{{$item['sisa']}}</td>
                                    <td>
                                        <a href="{{ route('report.departemen.travel.detail', ['id_departemen' => $item['id']]) }}" class="btn btn-success btn-xs"><i class="fas fa-eye"></i> Details</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
{{--                    <div class="card-footer text-right">--}}
{{--                        <a href="{{url()->previous()}}" class="btn btn-danger"><i class="fas fa-arrow-left"></i> Kembali</a>--}}
{{--                    </div>--}}
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
{{--                <div class="row">--}}
{{--                    <div class="col-md-12">--}}
{{--                        <!-- small card -->--}}
{{--                        <div class="small-box bg-info">--}}
{{--                            <div class="inner">--}}
{{--                                <h3>{{$saldo}}</h3>--}}

{{--                                <p>Actual Expense</p>--}}
{{--                            </div>--}}
{{--                            <div class="icon">--}}
{{--                                <i class="fas fa-money-bill-wave"></i>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>
            <!-- /.col -->
        </div>
    </div>
@stop

@push('js')<script>$(() => $("#drPlaceholder").val(''))</script>@endpush
