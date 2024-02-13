<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct (){
        $this->middleware('auth');
    }

    public function index(){
        $user = User::where('id', Auth::id())->first();
        $cek = UserSalary::where('user_id', Auth::id())->first();
//        dd($cek);
        $salary = UserSalary::where('user_id', Auth::id())->where('jumlah', '!=', null)->where('year', date('Y'))->
            whereHas('allowances', function ($query){
                $query->where('jenis', 'report')->where('tipe', 'personal');
            })->get();
//        dd($salary);
        $dept = UserSalary::where('user_id', Auth::id())->where('jumlah', '!=', null)->where('year', date('Y'))->
        whereHas('allowances', function ($query){
            $query->where('tipe', 'departemen');
        })->get();
        $monthly = UserSalary::where('user_id', Auth::id())->where('jumlah', '!=', null)->where('year', date('Y'))->
            whereHas('allowances', function ($query){
                $query->where('tipe', 'personal')->where('kategori', 'monthly');
            })->get();
        return view('personal.profile.index',compact('user', 'salary', 'monthly', 'dept', 'cek'));
    }
}
