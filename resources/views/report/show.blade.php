@extends('adminlte::page')

@section('title', 'SIPEKAN | Keuangan Departemen')

@section('content_header')
    <h1 class="m-0 text-dark">Keuangan Departemen {{$departemen->nama_departemen}}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- /.col -->
            <div class="col-md-9">
                <!-- /.card -->
                <div class="card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Tabel Keuangan Departemen </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>{{strtoupper('Account Name')}}</th>
                                <th>{{strtoupper('Account Code')}}</th>
                                <th>{{strtoupper('Budget')}}</th>
                                <th>{{strtoupper('Actual')}}</th>
                                <th>{{strtoupper('cash/Travel advance')}}</th>
                                <th>{{strtoupper('Sisa Budget')}}</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th>1.</th>
                                <th>Travel Expense</th>
                                <td>{{$departemen->travel_expense_code ?? ''}}</td>
                                <td class="text-right">{{$keuangan['travel_budget']}}</td>
                                <td class="text-right">{{$keuangan['travel_actual']}}</td>
                                <td class="text-right">{{$keuangan['travel_advance']}}</td>
                                <td class="text-right">{{$keuangan['sisa_travel']}}</td>
                                <td class="text-center">
                                    <a href="{{route('report.departemen.show.detail', ['jenis' => 'travel', 'id_departemen'=>$departemen->id])}}" class="btn btn-outline-info btn-sm">Details</a>
                                </td>
                            </tr>
                            <tr>
                                <th>2.</th>
                                <th>Special Travel</th>
                                <td>{{$departemen->travel_special_code}}</td>
                                <td class="text-right">{{$keuangan['special_travel_budget']}}</td>
                                <td class="text-right">{{$keuangan['special_travel_actual']}}</td>
                                <td class="text-right">-</td>
                                <td class="text-right">{{$keuangan['sisa_special_travel']}}</td>
                                <td class="text-center">
                                    <a href="{{route('report.departemen.show.detail', ['jenis' => 'special', 'id_departemen'=>$departemen->id])}}" class="btn btn-outline-info btn-sm">Details</a>
                                </td>
                            </tr>
                            <tr>
                                <th>3.</th>
                                <th>Strategic Plan</th>
                                <td>{{$departemen->strategic_plan_code}}</td>
                                <td class="text-right">{{$keuangan['strategic_budget']}}</td>
                                <td class="text-right">{{$keuangan['strategic_actual']}}</td>
                                <td class="text-right">-</td>
                                <td class="text-right">{{$keuangan['sisa_strategic']}}</td>
                                <td class="text-center">
                                    <a href="{{route('report.departemen.show.detail', ['jenis' => 'strategic', 'id_departemen'=>$departemen->id])}}" class="btn btn-outline-info btn-sm">Details</a>
                                </td>
                            </tr>
                            <tr>
                                <th>4.</th>
                                <th>Office Expense</th>
                                <td>{{$departemen->office_expense_code}}</td>
                                <td class="text-right">{{$keuangan['office_budget']}}</td>
                                <td class="text-right">{{$keuangan['office_actual']}}</td>
                                <td class="text-right">-</td>
                                <td class="text-right">{{$keuangan['sisa_office']}}</td>
                                <td class="text-center">
                                    <a href="{{route('report.departemen.show.detail', ['jenis' => 'office', 'id_departemen'=>$departemen->id])}}" class="btn btn-outline-info btn-sm">Details</a>
                                </td>
                            </tr>
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
