@extends('adminlte::page')

@section('title', 'SIPEKAN | Profile')

@section('content_header')
{{--    <h1 class="m-0 text-dark">PROFILE</h1>--}}
@stop

@section('content')
    <div class="container-fluid mt-3">
        <section class="content">
            <div class="row">
                <div class="col-md-3">
                    <button class="btn btn-primary btn-block mb-3">Standard Operating Procedures</button>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Folders</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-sm">
                                <tr>
                                    <td>
                                        1.
                                    </td>
                                    <td>
                                        Prosedur Pemberian Bantuan Dana ke Counterpart dari Dept atau Officers
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        2.
                                    </td>
                                    <td>
                                        Cash Advance
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Inbox</h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" placeholder="Search">
                                    <div class="input-group-append">
                                        <div class="btn btn-primary">
                                            <i class="fas fa-search"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-1">
                            <div class="table-responsive mailbox-messages">
                                <table class="table table-hover table-striped">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="icheck-primary">
                                                    <input type="checkbox" value id="check6">
                                                    <label for="check6"></label>
                                                </div>
                                            </td>
                                            <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a></td>
                                            <td class="mailbox-name"><a href="read-mail.html">Alexander Pierce</a></td>
                                            <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find a solution to this problem...</td>
                                            <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                            <td class="mailbox-date">2 days ago</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="icheck-primary">
                                                    <input type="checkbox" value id="check7">
                                                    <label for="check7"></label>
                                                </div>
                                            </td>
                                            <td class="mailbox-star"><a href="#"><i class="fas fa-star-o text-warning"></i></a></td>
                                            <td class="mailbox-name"><a href="read-mail.html">Alexander Pierce</a></td>
                                            <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find a solution to this problem...</td>
                                            <td class="mailbox-attachment"><i class="fas fa-paperclip"></i></td>
                                            <td class="mailbox-date">2 days ago</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="icheck-primary">
                                                    <input type="checkbox" value id="check8">
                                                    <label for="check8"></label>
                                                </div>
                                            </td>
                                            <td class="mailbox-star"><a href="#"><i class="fas fa-star text-warning"></i></a></td>
                                            <td class="mailbox-name"><a href="read-mail.html">Alexander Pierce</a></td>
                                            <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find a solution to this problem...</td>
                                            <td class="mailbox-attachment"></td>
                                            <td class="mailbox-date">2 days ago</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="icheck-primary">
                                                    <input type="checkbox" value id="check9">
                                                    <label for="check9"></label>
                                                </div>
                                            </td>
                                            <td class="mailbox-star"><a href="#"><i class="fas fa-star text-warning"></i></a></td>
                                            <td class="mailbox-name"><a href="read-mail.html">Alexander Pierce</a></td>
                                            <td class="mailbox-subject"><b>AdminLTE 3.0 Issue</b> - Trying to find a solution to this problem...</td>
                                            <td class="mailbox-attachment"></td>
                                            <td class="mailbox-date">2 days ago</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@stop
