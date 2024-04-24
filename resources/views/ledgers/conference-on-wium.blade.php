@extends('adminlte::page')

@section('title', 'SIPEKAN | Keuangan Personal')

@section('content_header')
    <h1 class="m-0 text-dark">Ledger Conf./Mission on WIUM</h1>
@stop

@section('content')
    {{--    <livewire:personal-financial />--}}
    <div class="container-fluid">
        <div class="row">
            <!-- /.col -->
            <div class="col-md-10">
                <!-- /.card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><strong>Details Ledgers</strong></h3>
{{--                        <a href="#" target="_blank" class="btn btn-sm btn-outline-warning float-right ml-2"><i class="fa fa-file-pdf"></i> PDF</a>--}}
{{--                        <form action="" method="GET" target="_blank">--}}
{{--                            <input type="hidden" name="pdf">--}}
{{--                            <input type="hidden" name="periode_awal" value="{{$periode_awal}}">--}}
{{--                            <input type="hidden" name="periode_akhir" value="{{$periode_akhir}}">--}}
{{--                            <button type="submit" class="btn btn-sm btn-outline-warning float-right ml-2"><i class="fa fa-print"></i> PDF</button>--}}
{{--                        </form>--}}
                        <form action="" method="GET" target="_blank">
                            <input type="hidden" name="print">
                            <input type="hidden" name="periode_awal" value="{{$periode_awal}}">
                            <input type="hidden" name="periode_akhir" value="{{$periode_akhir}}">
                            <button type="submit" class="btn btn-sm btn-outline-success float-right"><i class="fa fa-print"></i> Print / <i class="fa fa-file-pdf"></i> Pdf</button>
                        </form>
{{--                        <a href="{{ route('ledger.conference_on_wium.print') }}" target="_blank" class="btn btn-sm btn-outline-secondary float-right"><i class="fa fa-print"></i> Print</a>--}}
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0" style="height: 700px;">
                        <table class="table table-striped table-head-fixed">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Journal Number</th>
                                <th>Description</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-right"><strong>Opening Balance</strong></td>
                                <td></td>
                                <td></td>
                                <td class="text-right"><strong>{{$saldo_awal}}</strong></td>
                            </tr>
                            @foreach($list_keuangan_fix as $period => $list)
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><strong>Start Period {{ preg_replace('/(\d{4})(\d+)/', '$1/$2', $period) }}</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @foreach($list as $item)
                                    <tr>
                                        <td>{{date('d/m/Y', strtotime($item['tanggal']))}}</td>
                                        <td>{{$item['reference']}}</td>
                                        <td>{{$item['nomor_jurnal']}} - {{ $item['journal_line'] }}</td>
                                        <td>{{$item['description']}}</td>
                                        <td class="text-right">{{$item['debit']}}    </td>
                                        <td class="text-right">{{$item['credit']}}</td>
                                        <td class="text-right">{{$item['balance']}}    </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-2">
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
                                        <input type="month" name="periode_awal" value="{{$periode_awal}}" class="form-control"
                                               id="exampleInputEmail1">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Periode Akhir</label>
                                        <input type="month" name="periode_akhir" value="{{$periode_akhir}}" class="form-control"
                                               id="exampleInputPassword1">
                                    </div>
                                    <button type="submit" class="btn btn-primary float-right">Pilih Period</button>
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
                                <h4><strong>{{$saldo_akhir}},-</strong></h4>

                                <p>Ending Balance {{ date('F Y', strtotime($periode_akhir)) }}</p>
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

