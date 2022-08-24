@extends('adminlte::page')

@section('title', 'AdminLTE')

@section('content_header')
    <h1 class="m-0 text-dark">Keuangan Personal</h1>
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
                        <h3 class="card-title">Detail Keuangan Personal</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Jurnal No</th>
                                <th>Description</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="5" class="text-center"><strong>Opening Balance</strong></td>
                                <td class="text-right"><strong>{{$saldo_awal}}</strong></td>
                            </tr>
                            @foreach($list_keuangan as $item)
                                <tr>
                                    <td>{{$item['tanggal']}}</td>
                                    <td>{{$item['nomor_jurnal']}}</td>
                                    <td>{{$item['description']}}</td>
                                    <td class="text-right">{{$item['debit']}}	</td>
                                    <td class="text-right">{{$item['credit']}}</td>
                                    <td class="text-right">{{$item['balance']}}	</td>
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
                                <h3 class="card-title"><strong>FILTER</strong></h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <form method="GET">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Tanggal Awal</label>
                                        <input type="date" name="tgl_awal" value="{{$tgl_awal}}" class="form-control" id="exampleInputEmail1">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Tanggal Akhir</label>
                                        <input type="date" name="tgl_akhir" value="{{$tgl_akhir}}" class="form-control" id="exampleInputPassword1">
                                    </div>
                                    <button type="submit" class="btn btn-primary float-right">Pilih Tanggal</button>
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
                                <h3>{{$saldo_akhir}},-</h3>

                                <p>Saldo Akhir</p>
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
