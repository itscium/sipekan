@extends('adminlte::page')

@section('title', 'SIPEKAN | Profile')

@section('content_header')
    <h1 class="m-0 text-dark">PROFILE</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">

                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if(config('adminlte.usermenu_image'))
                                <img src="{{ Auth::user()->adminlte_image() }}"
                                     class="profile-user-img img-fluid img-circle"
                                     alt="{{ Auth::user()->name }}">
                            @endif
{{--                            <img class="profile-user-img img-fluid img-circle"--}}
{{--                                 src="{{asset('/dist/img/user4-128x128.jpg')}}"--}}
{{--                                 alt="User profile picture">--}}
                        </div>

                        <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>

{{--                        <p class="text-muted text-center">{{ $user->roles->jabatan->role }}</p>--}}
                        <p class="text-muted text-center">{{ Auth::user()->kepala_departemen->nama_departemen ?? '' }}</p>

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- About Me Box -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">About Me</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <strong><i class="fas fa-book mr-1"></i> Education</strong>

                        <p class="text-muted">
                            B.S. in Computer Science from the University of Tennessee at Knoxville
                        </p>

                        <hr>

                        <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>

                        <p class="text-muted">Malibu, California</p>

                        <hr>

                        <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>

                        <p class="text-muted">
                            <span class="tag tag-danger">UI Design</span>
                            <span class="tag tag-success">Coding</span>
                            <span class="tag tag-info">Javascript</span>
                            <span class="tag tag-warning">PHP</span>
                            <span class="tag tag-primary">Node.js</span>
                        </p>

                        <hr>

                        <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>

                        <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Settings</a></li>
                            @if($cek !== null)
                            <li class="nav-item"><a class="nav-link" href="#salary" data-toggle="tab">Salary & Allowances</a></li>
                            @can('head_dept')
                                <li class="nav-item"><a class="nav-link" href="#departemen" data-toggle="tab">Departement Budget</a></li>
                            @endcan
                            @endif
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="settings">
                                <form class="form-horizontal">
                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Name</label>
                                        <div class="col-sm-10">
                                            <input type="email" class="form-control" id="inputName" placeholder="Name">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" class="form-control" id="inputEmail" placeholder="Email">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputName2" class="col-sm-2 col-form-label">Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputName2" placeholder="Name">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputExperience" class="col-sm-2 col-form-label">Experience</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" id="inputExperience" placeholder="Experience"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="inputSkills" class="col-sm-2 col-form-label">Skills</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="inputSkills" placeholder="Skills">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox"> I agree to the <a href="#">terms and conditions</a>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <button type="submit" class="btn btn-danger">Submit</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane" id="salary">
                                <div class="row">
                                    <div class="col-md-6">
                                        <fieldset class="border p-2 mb-2">
                                            <legend class="w-auto">By Report</legend>
                                            <table class="table table-sm">
                                                @foreach($salary as $item)
                                                    <tr>
                                                        <th>{{strtoupper($item->allowances->nama)}}</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <td class="text-right">{{number_format($item->jumlah)}}{{$item->satuan ?? ''}}</td>
                                                        <td>{{$item->keterangan}}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <fieldset class="border p-2 mb-2">
                                            <legend class="w-auto">Monthly Allowances</legend>
                                            <table class="table table-sm">
                                                @foreach($monthly as $item)
                                                    <tr>
                                                        <th>{{strtoupper($item->allowances->nama)}}</th>
                                                        <td class="text-right">{{number_format($item->jumlah)}}{{$item->satuan ?? ''}}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                            <dl class="row" style="display: none">
                                                <dt class="col-sm-2">Wagefactor</dt>
                                                <dd class="col-sm-1 text-right">67.500</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">Optical Allowances</dt>
                                                <dd class="col-sm-1 text-right">After 75%</dd>
                                                <dd class="col-sm-8">Max 2.500.000 for Reinbursment</dd>
                                                <dt class="col-sm-12">Medical Allowances</dt>
                                                <dt class="col-sm-2">&nbsp;&nbsp; - OPD</dt>
                                                <dd class="col-sm-1 text-right">75%</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">&nbsp;&nbsp; - IPD</dt>
                                                <dd class="col-sm-1 text-right">90%</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">&nbsp;&nbsp; - Tarif Kamar</dt>
                                                <dd class="col-sm-1 text-right">1.200.000</dd>
                                                <dd class="col-sm-8">Officers</dd>
                                                <dd class="col-sm-1 offset-sm-2 text-right">900.000</dd>
                                                <dd class="col-sm-8">Dept., Assc., Asst.,Contr., HRD,Chief</dd>
                                                <dd class="col-sm-1 offset-sm-2 text-right">750.000</dd>
                                                <dd class="col-sm-8">Staff</dd>
                                                <dt class="col-sm-2">Educational Allowances</dt>
                                                <dd class="col-sm-1 text-right">60%</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">Road Tax</dt>
                                                <dd class="col-sm-1 text-right">3.000.000</dd>
                                                <dd class="col-sm-8">Max</dd>
                                                <dt class="col-sm-2">Car Insurance</dt>
                                                <dd class="col-sm-1 text-right">5.000.000</dd>
                                                <dd class="col-sm-8">Max</dd>
                                                <dt class="col-sm-2">Perdiem</dt>
                                                <dd class="col-sm-1 text-right">350.000</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">Single Meal</dt>
                                                <dd class="col-sm-1 text-right">87.500</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-12">Milage/km - Pertalite/liter (Base Market)</dt>
                                                <dt class="col-sm-2">&nbsp;&nbsp; - Car Authorized</dt>
                                                <dd class="col-sm-1 text-right">2.460</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">&nbsp;&nbsp; - Car Unauthorized</dt>
                                                <dd class="col-sm-1 text-right">3.040</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">&nbsp;&nbsp; - Scooter Authorized</dt>
                                                <dd class="col-sm-1 text-right">775</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">&nbsp;&nbsp; - Scooter Unauthorized</dt>
                                                <dd class="col-sm-1 text-right">1.000</dd>
                                                <dd class="col-sm-8"></dd>
                                                <dt class="col-sm-2">Hotel</dt>
                                                <dd class="col-sm-1 text-right">600.000</dd>
                                                <dd class="col-sm-8">Max/night</dd>
                                                <dt class="col-sm-2">Milage</dt>
                                                <dd class="col-sm-1 text-right">800 KM</dd>
                                                <dd class="col-sm-8">PP Max</dd>
                                            </dl>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="departemen">
                                <div class="row">
                                    <div class="col-md-12">
                                        <fieldset class="border p-2 mb-2">
                                            <legend class="w-auto">Budget Departemen</legend>
                                            <table class="table table-sm">
                                                @foreach($dept as $item)
                                                    <tr>
                                                        <th>{{strtoupper($item->allowances->nama)}}</th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <th></th>
                                                        <td class="text-right">{{number_format($item->jumlah)}}{{$item->satuan ?? ''}}</td>
                                                        <td>{{$item->keterangan}}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div><!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
@stop
