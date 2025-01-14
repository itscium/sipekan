@extends('adminlte::page')

@section('title', 'SIPEKAN | Profile')

@section('content_header')
    {{--    <h1 class="m-0 text-dark">PROFILE</h1>--}}
@stop

@section('content')
    <div class="container-fluid">
        <section class="content">
            <div class="row">
                <div class="col-md-3 mt-3">
                    <button class="btn btn-primary btn-block mb-3" disabled>Standard Operating Procedures</button>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">SOP Lists</h3>
                            <div class="card-tools">
                                <a href="{{ route('sop.create') }}" class="btn btn-secondary btn-xs">
                                    <i class="fas fa-plus-circle"></i> Add
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-sm">
                                @foreach($sops as $index=>$item)
                                    <tr>
                                        <td>
                                            {{ $index+1 }}
                                        </td>
                                        <td>
                                            <a href="#">{{ $item->nama }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-9 mt-3">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Content</h3>
                            <div class="card-tools">
                                {{--                                <div class="input-group input-group-sm">--}}
                                {{--                                    <input type="text" class="form-control" placeholder="Search">--}}
                                {{--                                    <div class="input-group-append">--}}
                                {{--                                        <div class="btn btn-primary">--}}
                                {{--                                            <i class="fas fa-search"></i>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                            </div>
                        </div>
                        <div class="card-body p-lg-4">
                            {!! $select->isi !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@stop
