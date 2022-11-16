<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct (){
        $this->middleware('auth');
    }

    public function index(){
        $user = User::where('id', Auth::id())->first();
        return view('personal.profile.index',compact('user'));
    }
}
