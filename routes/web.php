<?php

use App\Http\Controllers\DepartemenController;
use App\Http\Controllers\Personal\KeuanganController;
use App\Http\Controllers\Personal\ProfileController;
use App\Http\Controllers\WilayahController;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
//    return view('welcome');
});

Auth::routes(['register'=>false]);

Route::middleware('auth')->group(function (){
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
    Route::resource('departemens', DepartemenController::class);
    Route::prefix('wilayah')->group( function (){
        Route::get('/', [WilayahController::class, 'index'])->name('wilayah.index');
        Route::get('/edit/{id}', [WilayahController::class, 'edit'])->name('wilayah.edit');
        Route::get('/{id}/departemen', [WilayahController::class, 'departemen'])->name('wilayah.departemen');
        Route::get('/{id}/departemen/tambah', [WilayahController::class, 'tambah_departemen'])->name('wilayah.departemen.tambah');
        Route::post('/departemen/simpan', [WilayahController::class, 'simpan_departemen'])->name('wilayah.departemen.simpan');
        Route::get('/departemen/{id}/edit', [WilayahController::class, 'edit_departemen'])->name('wilayah.departemen.edit');
        Route::post('/departemen/update', [WilayahController::class, 'update_departemen'])->name('wilayah.departemen.update');
        Route::get('/{id}/users', [WilayahController::class, 'pengguna'])->name('wilayah.pengguna');
        Route::get('/{id}/users/tambah', [WilayahController::class, 'tambah_pengguna'])->name('wilayah.pengguna.tambah');
        Route::post('/users/simpan', [WilayahController::class, 'simpan_pengguna'])->name('wilayah.pengguna.simpan');
        Route::get('/users/{id}/edit', [WilayahController::class, 'edit_pengguna'])->name('wilayah.pengguna.edit');
        Route::post('/users/update', [WilayahController::class, 'update_pengguna'])->name('wilayah.pengguna.update');
        Route::get('/{id}/roles', [WilayahController::class, 'role'])->name('wilayah.roles');
        Route::get('/{id}/roles/tambah', [WilayahController::class, 'tambah_role'])->name('wilayah.roles.tambah');
        Route::post('/roles/simpan', [WilayahController::class, 'simpan_role'])->name('wilayah.roles.simpan');
    });
    Route::prefix('personal')->group( function (){
        Route::get('/keuangan', [KeuanganController::class, 'index'])->name('personal.keuangan.index');
        Route::get('/travel', [KeuanganController::class, 'travel'])->name('personal.keuangan.travel');
        Route::get('/profile', [ProfileController::class, 'index'])->name('personal.profile.index');
        Route::get('/change-password', [\App\Http\Controllers\Personal\ChangePasswordController::class, 'index'])->name('personal.change-password.index');
        Route::post('/change-password', [\App\Http\Controllers\Personal\ChangePasswordController::class, 'update_password'])->name('personal.change-password.update');
    });
    Route::prefix('departemen')->group( function (){
        Route::get('/pegawai', [App\Http\Controllers\Departemen\DepartemenController::class, 'index'])->name('departemen.pegawai.index');
        Route::get('/keuangan', [App\Http\Controllers\Departemen\KeuanganController::class, 'index'])->name('departemen.keuangan.index');
        Route::get('/keuangan/{jenis}/detail', [App\Http\Controllers\Departemen\KeuanganController::class, 'detail_keuangan'])->name('departemen.keuangan.detail');
    });
    Route::prefix('report')->group( function (){
        Route::get('/departemen', [\App\Http\Controllers\Report\DepartemenExpenseController::class, 'index'])->name('report.departemen');
        Route::get('/departemen/{id}/show', [\App\Http\Controllers\Report\DepartemenExpenseController::class, 'show'])->name('report.departemen.show');
        Route::get('/{jenis}/departemen/{id_departemen}/details', [\App\Http\Controllers\Report\DepartemenExpenseController::class, 'details'])->name('report.departemen.show.detail');
    });

    Route::get('/impersonate/{id}', [\App\Http\Controllers\ImpersonateController::class, 'impersonate'])->name('impersonate');
    Route::delete('/impersonate/destroy', [\App\Http\Controllers\ImpersonateController::class, 'destroy'])->name('impersonate.destroy');
});

//Auth::routes();
//
//Route::get('/home', function() {
//    return view('home');
//})->name('home')->middleware('auth');
