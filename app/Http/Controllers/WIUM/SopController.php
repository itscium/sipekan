<?php

namespace App\Http\Controllers\WIUM;

use App\Http\Controllers\Controller;
use App\Models\SOP;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $sops = SOP::all();
        $select = SOP::first();
        return view('wium.sop.index', compact('sops', 'select'));
    }

    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view('wium.sop.create');
    }

    public function store(Request $request)
    {
        $request->validate([
           'name' => 'required',
           'description' => 'required',
        ]);
//        dd($request->all());
        $sop = new SOP();
        $sop->nama = $request->get('name');
        $sop->isi = $request->get('description');
        $sop->save();
        return redirect()->route('sop.index');
    }
}
