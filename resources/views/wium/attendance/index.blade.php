@extends('adminlte::page')

@section('title', 'SIPEKAN | Attendance')

@section('content_header')
    <h1 class="m-0 text-dark">Fingerprint Attendance</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-9">
                <div class="card">
                    <div class="card-body table-responsive">
                        {{--                    <a href="{{route('departemens.create')}}" class="btn btn-primary mb-2">--}}
                        {{--                        Tambah Departemen--}}
                        {{--                    </a>--}}
                        <table class="table table-hover table-bordered table-stripped" id="example2">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Days</th>
                                <th>Time In</th>
                                <th>IN Status</th>
                                <th>Time OUT</th>
                                <th>OUT Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($mergedData as $item)
                                <tr>
                                    <td>{{$item['date']}}</td>
                                    <td>{{date('l', strtotime($item['date']))}}</td>
                                    <td class="{{ $item['time_in'] == null ? 'text-danger':'' }}  {{$item['instatus'] == 'late'?'text-danger':''}}">
                                        {{ $item['time_in'] == null ? 'No Fingerprint Available':date('H:i:s',strtotime($item['time_in'])) }}
                                    </td>
                                    @if($item['instatus'] == 'late')
                                        <td class="text-danger">
                                            <strong>{{ strtoupper($item['instatus']) }}</strong>
                                        </td>
                                    @elseif($item['instatus'] == 'not late')
                                        <td class="text-success">
                                            {{ ucwords($item['instatus']) }}
                                        </td>
                                    @else
                                        <td class="text-danger">
                                            {{ ucwords($item['instatus']) }}
                                        </td>
                                    @endif

                                    <td class="{{ $item['time_out'] == null ? 'text-danger':'' }} {{$item['outstatus'] == 'early'?'text-danger':''}}">
                                        {{ $item['time_out'] == null ? 'No Fingerprint Available':date('H:i:s',strtotime($item['time_out'])) }}
                                    </td>
                                    @if($item['outstatus'] == 'early')
                                        <td class="text-danger">
                                            <strong>{{ strtoupper($item['outstatus']) }}</strong>
                                        </td>
                                    @elseif($item['outstatus'] == 'not early')
                                        <td class="text-success">
                                            {{ ucwords($item['outstatus']) }}
                                        </td>
                                    @else
                                        <td class="text-danger">
                                            {{ ucwords($item['outstatus']) }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <!-- /.card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><strong>FILTER</strong></h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="GET">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Start Date</label>
                                <input type="date" name="tgl_awal" class="form-control" id="exampleInputEmail1">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputPassword1">End Date</label>
                                <input type="date" name="tgl_akhir" class="form-control" id="exampleInputPassword1">
                            </div>
                            <button type="submit" class="btn btn-primary float-right" disabled>Coming Soon!!</button>
                        </form>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@stop
