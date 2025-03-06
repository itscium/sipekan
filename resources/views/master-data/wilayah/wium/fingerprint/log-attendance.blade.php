@extends('adminlte::page')

@section('title', 'SIPEKAN | Log Attendance')

@section('content_header')
    <h1 class="m-0 text-dark">Log Attendance</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-responsive">
                        <a href="{{ route('wilayah.fingerprint-machine.get-log-attendance') }}" class="btn btn-primary mb-2">
                            Get Log Attendance from Machine <i class="fa fa-download"></i>
                        </a>
                        <table class="table table-hover table-bordered table-stripped" id="example2">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>FP User ID</th>
                                <th>Datetime</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th class="text-center">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_log as $key => $item)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$item->pin}}</td>
                                    <td>{{$item->datetime}}</td>
                                    <td>{{$item->date}}</td>
                                    <td>{{$item->time}}</td>
                                    <td>{{$item->status}}</td>
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

@push('js')
    <form action="" id="delete-form" method="post">
        @method('delete')
        @csrf
    </form>
    <script>
        $('#example2').DataTable({
            "responsive": true,
        });
        function notificationBeforeDelete(event, el) {
            event.preventDefault();
            if (confirm('Apakah anda yakin akan menghapus data ? ')) {
                $("#delete-form").attr('action', $(el).attr('href'));
                $("#delete-form").submit();
            }
        }
    </script>
@endpush
