<?php

namespace App\Http\Controllers\WIUM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('wium.sop');
    }
}
