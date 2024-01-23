@extends('adminlte::page')

@section('title', 'SIPEKAN | Keuangan Departemen')

@section('content_header')
    <h1 class="m-0 text-dark">LAPORAN {{ strtoupper($allowance->nama) }} DEPARTEMEN</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- /.col -->
            <div class="col-md-9">
                <!-- /.card -->
                <div class="card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Tabel Laporan {{ ucwords($allowance->nama) }} </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0" style="height: 700px">
                        <table class="table table-striped table-sm table-">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>{{strtoupper('Department Name')}}</th>
{{--                                <th>{{strtoupper('Account Code')}}</th>--}}
                                <th>{{strtoupper('Budget')}}</th>
                                <th>{{strtoupper('Actual')}}</th>
                                @if($allowance->account_code === '822110')
                                <th>{{strtoupper('cash/Travel advance')}}</th>
                                @endif
                                <th>{{strtoupper('Sisa Budget')}}</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($data_report as $index=>$item)
                                <tr>
                                    <th>{{$index+1}}</th>
                                    <th>{{$item['nama_departemen']}}</th>
                                    {{--                                    <td>{{$departemen->travel_expense_code ?? ''}}</td>--}}
                                    <td>{{$item['budget']}}</td>
                                    <td>{{$item['actual']}}</td>
                                    @if($allowance->account_code === '822110')
                                    <td>{{$item['travel_advance']}}</td>
                                    @endif
                                    <td>{{$item['sisa']}}</td>
                                    <td>
                                        <a href="{{route('report.departemen.show.detail', ['jenis'=>$allowance->id ,'id_departemen'=>$item['id']])}}" class="btn btn-outline-info btn-sm">Details</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{route('report.departemen')}}" class="btn btn-danger"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                    {{--                    <div class="col-md-12">--}}
                    {{--                        <!-- small card -->--}}
                    {{--                        <div class="small-box bg-info">--}}
                    {{--                            <div class="inner">--}}
                    {{--                                <h3>Rp. 25,000,000,-</h3>--}}

                    {{--                                <p>Saldo Akhir</p>--}}
                    {{--                            </div>--}}
                    {{--                            <div class="icon">--}}
                    {{--                                <i class="fas fa-money-bill-wave"></i>--}}
                    {{--                            </div>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}
                </div>
            </div>
            <!-- /.col -->
        </div>
    </div>
@stop

@push('js')<script>$(() => $("#drPlaceholder").val(''))</script>@endpush
