<?php

namespace App\Http\Controllers\WIUM\Allowance;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalController extends Controller
{
    public function __construct (){
        $this->middleware('auth');
    }

    public function index(){
        $wilayah = Wilayah::where('id', Auth::user()->wilayah_id)->first();
        $users = User::where('wilayah_id', Auth::user()->wilayah_id)->where('status', 1)->get();
        return view('wium.allowance.personal.index', compact('users', 'wilayah'));
    }
}
