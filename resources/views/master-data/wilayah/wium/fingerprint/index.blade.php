@extends('adminlte::page')

@section('title', 'SIPEKAN | FPMachine')

@section('content_header')
    <h1 class="m-0 text-dark">Fingerprint Machine</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-responsive">
                        {{--                    <a href="{{route('departemens.create')}}" class="btn btn-primary mb-2">--}}
                        {{--                        Tambah Departemen--}}
                        {{--                    </a>--}}
                        <table class="table table-hover table-bordered table-stripped" id="example2">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>IP</th>
                                <th>Port</th>
                                <th>Location</th>
                                <th class="text-center">Opsi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_machine as $key => $item)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$item->ip_address}}</td>
                                    <td>{{$item->port}}</td>
                                    <td>{{$item->location}}</td>
                                    <td class="text-center">
                                        <a href="{{ route('wilayah.fingerprint-machine.list-user', ['id_wilayah' => 1, 'id_machine' => $item->id ]) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i>
                                            List User
                                        </a>
                                        <a href="{{ route('wilayah.fingerprint-machine.log-attendance', ['id_wilayah' => 1, 'id_machine' => $item->id ]) }}" class="btn btn-info btn-sm"><i class="fa fa-fingerprint"></i>
                                            Log Attendance
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@stop
