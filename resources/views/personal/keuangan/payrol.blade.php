@extends('adminlte::page')

@section('title', 'SIPEKAN | Payrol')

@section('content_header')
    <h1 class="m-0 text-dark">Detail Payroll</h1>
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
                        <a href="{{url('personal/keuangan')}}" class="btn btn-danger"><i class="fa fa-backward"></i> Back</a>
                        <a href="{{route('personal.keuangan.payrol.pdf', $period)}}" target="_blank" class="btn btn-outline-success float-right"><i class="fa fa-print"></i> Print</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-head-fixed">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Description</th>
                                <th class="text-right">Earnings</th>
                                <th class="text-right">Deductions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($payrol as $index=>$item)
                                <tr>
                                    <td>{{$index+1}}</td>
                                    <td>{{$item->allowance_name}}</td>
                                    <td class="text-right">{{$item->signal === '+' ? number_format($item->value) : ''}}</td>
                                    <td class="text-right">{{$item->signal === '-' ? number_format($item->value) : ''}}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Payrol Belum Diposting oleh Keuangan</td>
                                </tr>
                            @endforelse
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
                                        <input type="month" name="periode" onchange="this.form.submit()" value="{{$period}}" class="form-control" id="exampleInputEmail1">
{{--                                                                                value="{{$tgl_akhir}}"--}}
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
                                <h3>Rp. {{ number_format($net->value ?? 0) }}</h3>

                                <p>Net Payment</p>
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

{{--@push('js')<script>$(() => $("#drPlaceholder").val(''))</script>@endpush--}}
@push('js')
    {{--    <script>$(() => $("#drPlaceholder").val(''))</script>--}}
    <script>
        let msg = '{{Session::get('alert')}}';
        let exist = "{{Session::has('alert')}}";
        if(exist){
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: msg,
                showConfirmButton: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Wokeeeh <i class="fa fa-thumbs-up"></i>',
            }).then((result) => {
                if (result.isConfirmed){
                    setTimeout(function(){ window.close();},300);
                }
            })
        }
    </script>
@endpush
