<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    public function impersonate( $id ){

        session()->start();
        session()->put('impersonate', Auth::id());
        session()->save();
        Auth::logout();
        Auth::loginUsingId($id);
        return redirect()->route('dashboard');
    }
    public function destroy(){
//        Auth::logout();
//        $id = session('impersonate');
//        dd($id);
        session()->forget('impersonate');
        Auth::loginUsingId(1);

        return redirect()->route('wilayah.index');
    }
}
