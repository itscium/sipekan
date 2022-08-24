<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct (){
        $this->middleware('auth');
    }

    public function index(){
        return view('personal.profile.index');
    }
}
