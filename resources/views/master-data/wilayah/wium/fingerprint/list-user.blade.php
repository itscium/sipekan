@extends('adminlte::page')

@section('title', 'SIPEKAN | List FP Machine User')

@section('content_header')
    <h1 class="m-0 text-dark">Fingerprint Machine List User</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body table-responsive">
                        <a href="{{route('wilayah.fingerprint-machine.get-user')}}" class="btn btn-primary btn-sm mb-2">
                            Get User From Machine <i class="fa fa-download"></i>
                        </a>
                        <a href="{{route('wilayah.fingerprint-machine', 1)}}" class="btn btn-danger btn-sm mb-2 float-right">
                            Back <i class="fa fa-backward"></i>
                        </a>
                        <table class="table table-hover table-bordered table-stripped" id="example2">
                            <thead>
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>User ID on Machine</th>
                                <th>User ID on Sipekan</th>
                                <th>Status</th>
                                <th class="text-center">Opsi</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list_user as $key => $item)
                                @php
                                    $ada = \App\Models\User::where('name', 'like', "%$item->name%")->where('wilayah_id', 1)->first()
                                @endphp
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{ ucwords($item->name) }}</td>
                                    <td>{{$item->pin}}</td>
                                    <td>{{ $ada->name ?? '' }} {{ $ada->id ?? '' }}</td>
                                    <td class="text-{{ $item->user_id == null ? 'danger': 'success' }}"><i class="fa fa-{{ $item->user_id == null ? 'ban': 'check' }}"> {{ $item->user_id == null ? 'Not Linked': 'Linked' }}</i></td>
                                    <td class="text-center">
                                        @if($ada && $item->user_id == null)
                                            <a href="{{ route('wilayah.fingerprint-machine.hubungkan-user', ['id_user'=>$ada->id, 'id' => $item->id ]) }}" class="btn btn-warning btn-sm"><i class="fa fa-link"></i>
                                                Hubungkan
                                            </a>
                                        @elseif(!$ada && $item->user_id == null)
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modal-sm-{{$item->id}}"><i class="fa fa-link"></i>
                                                Cari User
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                <div class="modal fade" id="modal-sm-{{$item->id}}">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <form action="{{ route('wilayah.fingerprint-machine.connect-user') }}" method="post">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">List User Sipekan</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <x-adminlte-select2 name="user_id">
                                                        <option value="" selected disabled>Pilih User</option>
                                                        @foreach($user_sipekan as $user)
                                                            <option value="{{$user->id}}">{{$user->name}}</option>
                                                        @endforeach
                                                    </x-adminlte-select2>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <input type="hidden" name="id" value="{{$item->id}}">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                        <!-- /.modal-content -->
                                    </div>
                                    <!-- /.modal-dialog -->
                                </div>
                                <!-- /.modal -->
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
