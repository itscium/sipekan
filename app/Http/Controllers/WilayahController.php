<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WilayahController extends Controller
{
    public function __construct (){
        $this->middleware('auth');
    }

    public function index()
    {
        $wilayah = Wilayah::all();
        return view('master-data.wilayah.index', compact('wilayah'));
    }

    public function edit($id){
        return null;
    }

    public function departemen($id){
        $wilayah = Wilayah::find($id);
        $departemens = Departemen::where('wilayah_id', $id)->get();
        return view('master-data.wilayah.wilayah-departemen', compact('departemens', 'id', 'wilayah'));
    }

    public function tambah_departemen ($id){
        $wilayah = Wilayah::find($id);
        $users = User::where('wilayah_id', $id)->get();
        return view('master-data.wilayah.wilayah-departemen-tambah', compact('wilayah', 'users'));
    }

    public function simpan_departemen (Request $request){
        $departemen = new Departemen();
        $departemen->nama_departemen = $request->nama_departemen;
        $departemen->kepala_departemen = $request->kepala_departemen;
        $departemen->wilayah_id = $request->wilayah_id;
        $departemen->department_code = $request->kode_akun_departemen;
        $departemen->travel_expense_code = $request->kode_akun_travel;
        $departemen->travel_special_code = $request->kode_akun_special_travel;
        $departemen->strategic_plan_code = $request->kode_akun_strategic_plan;
        $departemen->office_expense_code = $request->kode_akun_office;
        if ($departemen->save()){
            return redirect(route('wilayah.departemen', $request->wilayah_id));
        }
    }

    public function edit_departemen($id){
        $departemen = Departemen::find($id);
        $users = User::where('wilayah_id', $id)->get();
        return view('master-data.wilayah.wilayah-departemen-edit', compact('departemen', 'users'));
    }

    public function update_departemen (Request $request){
        $update_departemen = Departemen::find($request->id);
        $update_departemen->nama_departemen = $request->edit_nama_departemen;
        $update_departemen->kepala_departemen = $request->edit_kepala_departemen;
        $update_departemen->department_code = $request->edit_kode_akun_departemen;
        $update_departemen->travel_expense_code = $request->edit_kode_akun_travel;
        $update_departemen->travel_special_code = $request->edit_kode_akun_special_travel;
        $update_departemen->strategic_plan_code = $request->edit_kode_akun_strategic_plan;
        $update_departemen->office_expense_code = $request->edit_kode_akun_office;
        if ($update_departemen->save()){
            return redirect(route('wilayah.departemen', $update_departemen->wilayah_id));
        }
    }

    public function pengguna($id) {
        $wilayah = Wilayah::find($id);
        $users = User::where('wilayah_id', $id)->get();
        return view('master-data.wilayah.wilayah-user', compact('users', 'wilayah'));
    }

    public function tambah_pengguna ($id){
        $wilayah = Wilayah::find($id);
        return view('master-data.wilayah.wilayah-user-tambah', compact('wilayah'));
    }

    public function simpan_pengguna (Request $request){
        $user = new User();
        $user->name = $request->nama;
        $user->email = $request->email;
        $user->type = 'user';
        $user->password = Hash::make('123456');
        $user->wilayah_id = $request->wilayah_id;
        $user->ACCNT_CODE = $request->kode_akun_personal;
        $user->travel_account = $request->kode_akun_travel;
        if ($user->save()){
            return redirect(route('wilayah.pengguna', $user->wilayah_id));
        }
    }

    public function edit_pengguna ($id) {
//        $wilayah = Wilayah::find($id);
        $users = User::find($id);
        return view('master-data.wilayah.wilayah-user-edit', compact('users'));
    }

    public function update_pengguna (Request $request) {
        $update_user = User::find($request->id);
        $update_user->name = $request->edit_nama;
        $update_user->email = $request->edit_email;
        $update_user->ACCNT_CODE = $request->edit_kode_akun_personal;
        $update_user->travel_account = $request->edit_kode_akun_travel;
        if ($update_user->save()){
            return redirect(route('wilayah.pengguna', $update_user->wilayah_id))->with('alert', 'Data berhasil Diupdate');
        }
    }
}
