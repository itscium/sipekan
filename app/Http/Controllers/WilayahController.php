<?php

namespace App\Http\Controllers;

use App\Imports\SalaryAllowancesImport;
use App\Imports\UserImport;
use App\Imports\UserSalaryImport;
use App\Models\Departemen;
use App\Models\DepartmentExpense;
use App\Models\Role;
use App\Models\SalaryAllowances;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

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
        $wilayah = Wilayah::findorFail($id);
        return view('master-data.wilayah.edit', compact('wilayah'));
    }

    public function update(Request $request){
        $update = Wilayah::find($request->id);
        $update->nama = $request->edit_nama;
        $update->kode = $request->edit_kode;
        $update->account_on_wium = $request->edit_account_on_wium;
        $update->save();
        return redirect(route('wilayah.index'));
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
//        dd($departemen->id);
        $users = User::where('wilayah_id', '=', $departemen->wilayah_id)->get();
//        dd($users->id);
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
        $user->save();
        alert()->success('Success','Pengguna Berhasil Ditambahkan');
        return redirect(route('wilayah.pengguna', $user->wilayah_id));
    }

    public function edit_pengguna ($id) {
//        $wilayah = Wilayah::find($id);
        $users = User::find($id);
        $get_user_role = UserRole::where('user_id', $id)->get();
        $user_role = [];
        foreach ($get_user_role as $index=>$item){
            $user_role[$index] = $item->role_id;
        }

        $role =  Role::where('wilayah_id', $users->wilayah_id)->get();
//        dd($user_role);
        return view('master-data.wilayah.wilayah-user-edit', compact('users', 'role', 'user_role'));
    }

    public function update_pengguna (Request $request) {
//        dd($request->role[1]);
        $update_user = User::find($request->id);
        $update_user->name = $request->edit_nama;
        $update_user->email = $request->edit_email;
        $update_user->ACCNT_CODE = $request->edit_kode_akun_personal;
        $update_user->travel_account = $request->edit_kode_akun_travel;
        if ($update_user->save()){
            UserRole::where('user_id', $request->id)->delete();
            if ($request->role !== null){
                foreach ($request->role as $iValue) {
                    $roles = new UserRole();
                    $roles->user_id = $request->id;
                    $roles->role_id = $iValue;
                    $roles->save();
                }
            }
        }
        return redirect(route('wilayah.pengguna', $update_user->wilayah_id))->with('alert', 'Data berhasil Diupdate');
    }

    public function role($id) {
        $wilayah = Wilayah::find($id);
        $roles = Role::where('wilayah_id', $id)->get();
        return view('master-data.wilayah.wilayah-role', compact('roles', 'wilayah'));
    }

    public function tambah_role ($id) {
        $wilayah = Wilayah::find($id);
        return view('master-data.wilayah.wilayah-role-tambah', compact('wilayah'));
    }

    public function simpan_role (Request $request) {
        $role = new Role();
        $role->role = $request->nama_role;
        $role->wilayah_id = $request->wilayah_id;
        if ($role->save()){
            return redirect(route('wilayah.roles', $role->wilayah_id));
        }
    }

    public function salary_allowances ($id){
        $wilayah = Wilayah::find($id);
        $salary = SalaryAllowances::where('wilayah_id', $id)->get();
        return view('master-data.wilayah.wilayah-salary', compact('salary', 'wilayah'));
    }

    public function tambah_salary ($id){
        $wilayah = Wilayah::find($id);
        return view('master-data.wilayah.wilayah-salary-tambah', compact('wilayah'));
    }

    public function import_salary (Request $request){
        Excel::import(new SalaryAllowancesImport(), $request->file('file'));
        return back();
    }
    public function import_user_salary (Request $request){
//        $file = $request->file('file');
//        (new UserSalaryImport)->import($file);
        Excel::import(new UserSalaryImport(), request()->file('file'));
        alert()->success('Success','User Salary & Allowances Berhasil Di-Import');
        return redirect()->back();
    }

    public function import_user(Request $request){
        Excel::import(new UserImport(), request()->file('file'));
        return redirect()->back()->with('alert', 'User Berhasil Di-Import');
    }

    public function simpan_salary (Request $request){
        $salary = new SalaryAllowances();
        $salary->wilayah_id = $request->wilayah_id;
        $salary->nama = strtolower($request->nama);
        $salary->tipe = strtolower($request->tipe);
        $salary->jenis = strtolower($request->jenis);
        $salary->kategori = strtolower($request->kategori);
        $salary->keterangan = $request->keterangan;
        $salary->save();
        return redirect(route('wilayah.salary', $salary->wilayah_id));
    }

    public function department_expense($id){
        $wilayah = Wilayah::find($id);
        $expense = DepartmentExpense::where('wilayah_id', $id)->get();
        return view('master-data.wilayah.wilayah-department-expense', compact('wilayah', 'expense'));
    }

    public function tambah_department_expense($id){
        $wilayah = Wilayah::find($id);
        return view('master-data.wilayah.wilayah-department-expense-tambah', compact('wilayah'));
    }

    public function simpan_department_expense(Request $request){
        $department = new DepartmentExpense();
        $department->wilayah_id = $request->wilayah_id;
        $department->nama = $request->nama_department_expense;
        $department->account_code = $request->account_code;
        $department->save();
        return redirect(route('wilayah.department-expense', $department->wilayah_id));
    }
    public function edit_department_expense($id){
        $department = DepartmentExpense::find($id);
        return view('master-data.wilayah.wilayah-department-expense-edit', compact('department'));
    }

    public function update_department_expense(Request $request){
        $update = DepartmentExpense::find($request->id);
        $update->nama = $request->edit_nama;
        $update->account_code = $request->edit_account_code;
        $update->save();
        return redirect(route('wilayah.department-expense', $update->wilayah_id));
    }
}
