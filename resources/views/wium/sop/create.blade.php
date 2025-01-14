@extends('adminlte::page')

@section('title', 'SIPEKAN | Create SOP')

@section('plugins.Summernote', true)
@section('adminlte_css')
{{--    <link rel="stylesheet" href="../../plugins/summernote/summernote-bs4.min.css">--}}
@endsection

@section('content_header')
    {{--    <h1 class="m-0 text-dark">PROFILE</h1>--}}
@stop

@section('content')
    <div class="container-fluid">
        <section class="content">
            <div class="row">
                <div class="col-md-12 mt-4">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                Form Menambahkan SOP
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <form action="{{ route('sop.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Nama SOP</label>
                                    <input type="text" class="form-control" id="nama" name="name" placeholder="Masukkan Nama SOP" required>
                                </div>
                                <div class="form-group">
                                    <label for="summernote">Isi SOP</label>
                                    <textarea id="summernote" name="description" class="form-control" required></textarea>
                                </div>
                            </div>
                            <div class="card-footer text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                                <a href="#" class="btn btn-danger">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.col-->
            </div>
        </section>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@stop

@section('adminlte_js')
{{--    <script src="../../plugins/summernote/summernote-bs4.min.js"></script>--}}
    <script>
        $(function () {
            $('#summernote').summernote({
                height: 350, //set editable area's height
                placeholder: 'Silahkan input Content untuk SOP',
                codemirror: { // codemirror options
                    theme: 'monokai'
                }
            });
        })
    </script>
@endsection

