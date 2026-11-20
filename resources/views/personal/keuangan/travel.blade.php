@extends('adminlte::page')

@section('title', 'SIPEKAN | Travel Personal')

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
                    <div class="card-body table-responsive p-0">
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
                                <td colspan="5" class="text-center"><strong>Saldo awal</strong></td>
                                <td class="text-right">@include('partials.sp-balance', ['value' => $saldo_awal, 'label' => true, 'kind' => 'travel'])</td>
                            </tr>
                            @foreach($list_keuangan as $item)
                                <tr>
                                    <td>{{$item['tanggal']}}</td>
                                    <td>{{$item['nomor_jurnal']}}-{{$item['baris_jurnal']}}</td>
                                    <td>{{$item['description']}}</td>
                                    <td class="text-right">{{$item['debit']}}	</td>
                                    <td class="text-right">{{$item['credit']}}</td>
                                    <td class="text-right">@include('partials.sp-balance', ['value' => $item['balance'], 'kind' => 'travel'])</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="sp-order-note">Diurutkan berdasarkan nomor jurnal, sehingga tanggal transaksi bisa tidak berurutan.</div>
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
                                        <label for="exampleInputEmail1">Periode Awal</label>
                                        <input type="month" name="tgl_awal" value="{{$periode_awal}}" class="form-control" id="exampleInputEmail1">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Periode Akhir</label>
                                        <input type="month" name="tgl_akhir" value="{{$periode_akhir}}" class="form-control" id="exampleInputPassword1">
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
                        @include('partials.sp-balance-card', ['value' => $saldo_akhir, 'kind' => 'travel', 'title' => 'Saldo akhir akun travel'])
                    </div>
                </div>
            </div>
            <!-- /.col -->
        </div>
    </div>
@stop

@push('js')<script>$(() => $("#drPlaceholder").val(''))</script>@endpush
