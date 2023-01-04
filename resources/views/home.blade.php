@extends('adminlte::page')

@section('title', 'SIPEKAN | Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="mb-0">You are logged in!</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <div class="float-right d-none d-sm-block">
        Created by Raminson Siregar
    </div>
    <strong>Copyright &copy; <script>document.write(new Date().getFullYear())</script> <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.
@stop

@push('js')
    <script>
        let msg = '{{Session::get('alert')}}';
        let exist = '{{Session::has('alert')}}';
        if(exist){
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: msg,
                showConfirmButton: false,
                timer: 2000
            })
        }
    </script>
@endpush
