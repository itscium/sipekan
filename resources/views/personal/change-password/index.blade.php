@extends('adminlte::page')

@section('title', 'SIPEKAN | Change Password')

@section('content_header')
{{--    <h1 class="m-0 text-dark">Change Password</h1>--}}
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mt-2">
                <form action="{{route('personal.change-password.update')}}" method="POST">
                    @csrf
                    <div class="card-header">
                        <h3 class="card-title">Change Password Form</h3>
                    </div>
                    <div class="card-body">
                            <x-adminlte-input name="old_password" label="Old Password" type="password" placeholder="Old Password" label-class="text-lightblue">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-key text-lightblue"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-input name="new_password" label="New Password" type="password" placeholder="New Password" label-class="text-lightblue">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-key text-lightblue"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                            <x-adminlte-input name="new_password_confirmation" label="New Password Confirmation" type="password" placeholder="New Password Confirmation" label-class="text-lightblue">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text">
                                        <i class="fas fa-key text-lightblue"></i>
                                    </div>
                                </x-slot>
                            </x-adminlte-input>
                    </div>
                    <div class="card-footer text-right">
                        <x-adminlte-button class="btn-flat" type="submit" label="Update" theme="warning" icon="fas fa-lg fa-save"/>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
