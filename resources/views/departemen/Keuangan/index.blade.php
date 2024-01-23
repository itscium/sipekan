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
                            @foreach($data_allowance as $index=>$item)
                                <tr>
                                    <th>{{$index+1}}</th>
                                    <th>{{ucwords($item['account_name'])}}</th>
                                    <td>{{$item['account_code']}}</td>
                                    <td class="text-right">{{$item['budget']}}</td>
                                    <td class="text-right">{{$item['actual']}}</td>
                                    <td class="text-right">{{ $item['account_code'] === '822110' ? $item['travel_advance'] : '-'}}</td>
                                    <td class="text-right">{{ $item['sisa']}}</td>
                                    <td class="text-center">
                                        <a href="{{route('departemen.keuangan.detail', ['id' => $item['id']])}}" class="btn btn-outline-info btn-sm">Details</a>
                                    </td>
                                </tr>
{{--                            @empty--}}
{{--                                <tr>--}}
{{--                                    <td colspan="8" class="text-center">Tidak ada Department Expenses</td>--}}
{{--                                </tr>--}}
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
