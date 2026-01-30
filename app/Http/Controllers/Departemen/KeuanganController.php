<?php

namespace App\Http\Controllers\Departemen;

use App\Http\Controllers\Controller;
use App\Models\Allowances;
use App\Models\Departemen;
use App\Models\DepartmentExpense;
use App\Models\WIUM\A_SALFLDG;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class KeuanganController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    function table_a ($wilayah_id){
        $table = '';
        switch ($wilayah_id){
            case 1:
                $table = 'dbo.CIU_A_SALFLDG';
                break;
            case 2:
                $table = 'dbo.JLC_A_SALFLDG';
                break;
            case 3:
                $table = 'dbo.NSM_A_SALFLDG';
                break;
            case 4:
                $table = 'dbo.CSM_A_SALFLDG';
                break;
            case 5:
                $table = 'dbo.SSM_A_SALFLDG';
                break;
            case 6:
                $table = 'dbo.WJC_A_SALFLDG';
                break;
            case 7:
                $table = 'dbo.CJM_A_SALFLDG';
                break;
            case 8:
                $table = 'dbo.EJC_A_SALFLDG';
                break;
            case 9:
                $table = 'dbo.WKD_A_SALFLDG';
                break;
            case 10:
                $table = 'dbo.EKM_A_SALFLDG';
                break;
            case 11:
                $table = 'dbo.NTM_A_SALFLDG';
                break;
            case 10002:
                $table = 'dbo.JBC_A_SALFLDG';
                break;
        }
        return $table;
    }
    function table_b ($wilayah_id){
        $table = '';
        switch ($wilayah_id){
            case 1:
                $table = 'dbo.ADV_B_SALFLDG';
                break;
            case 2:
                $table = 'dbo.JLC_B_SALFLDG';
                break;
            case 3:
                $table = 'dbo.NSM_B_SALFLDG';
                break;
            case 4:
                $table = 'dbo.CSM_B_SALFLDG';
                break;
            case 5:
                $table = 'dbo.SSM_B_SALFLDG';
                break;
            case 6:
                $table = 'dbo.WJC_B_SALFLDG';
                break;
            case 7:
                $table = 'dbo.CJM_B_SALFLDG';
                break;
            case 8:
                $table = 'dbo.EJC_B_SALFLDG';
                break;
            case 9:
                $table = 'dbo.WKD_B_SALFLDG';
                break;
            case 10:
                $table = 'dbo.EKM_B_SALFLDG';
                break;
            case 11:
                $table = 'dbo.NTM_B_SALFLDG';
                break;
            case 10002:
                $table = 'dbo.JBC_B_SALFLDG';
                break;
        }
        return $table;
    }

    function get_keuangan ($per_awal, $per_akhir, $id_allowance){
        $wilayah_id = Auth::user()->wilayah_id;
        $table_a = $this->table_a($wilayah_id);
        $table_b = $this->table_b($wilayah_id);
        $allowance = DepartmentExpense::where('id', $id_allowance)->first();

        $departemen = Departemen::where('kepala_departemen', Auth::id())->first();
        //get Travel Expense
        $travel_actual = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $allowance->account_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, $per_akhir])
            ->sum($table_a.'.AMOUNT');
        $travel_budget = (new A_SALFLDG)->setTable($table_b)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $allowance->account_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, date('Y'.'012')])
            ->sum($table_b.'.AMOUNT');
        $travel_advance = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->user->travel_account)
            ->where('PERIOD', '<=', $per_akhir)
            ->sum($table_a.'.AMOUNT');

        if ($allowance->account_code === '822110'){
            $temp = $travel_advance + $travel_actual;
            $sisa_travel = $travel_budget - $temp;
        }else{
            $sisa_travel = $travel_budget - $travel_actual;
        }

        //change into number format and remove (-)
        $travel_actual = number_format($travel_actual*-1);
        $travel_budget = number_format($travel_budget*-1);
        $sisa_travel = number_format($sisa_travel*-1);
        $travel_advance = number_format($travel_advance*-1);

        return [
            'travel_budget' => $travel_budget,
            'travel_actual' => $travel_actual,
            'sisa_travel' => $sisa_travel,
            'travel_advance' => $travel_advance,
        ];
    }

    function get_detail_keuangan ($per_awal, $per_akhir, $id_allowance){
        $wilayah_id = Auth::user()->wilayah_id;
        $table = $this->table_a($wilayah_id);

        $departemen = Departemen::where('kepala_departemen', Auth::id())->first();
//        $code = '';
        $code = DepartmentExpense::where('id', $id_allowance)->first();
        $detail = [];
        $keuangan = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $code->account_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal,$per_akhir])
            ->orderBy('PERIOD', 'DESC')
            ->get();
        foreach ($keuangan as $index=> $item){
            $detail[$index]['period'] = $item['PERIOD'];
            $detail[$index]['reference'] = $item['TREFERENCE'];
            $detail[$index]['amount'] = number_format($item['AMOUNT']*-1);
            $detail[$index]['description'] = $item['DESCRIPTN'];
            $detail[$index]['nomor_jurnal'] = $item['JRNAL_NO'];

        }

        return $detail;
    }

    public function index(){
        if (isset($_GET['periode'])){
            $per_awal = Carbon::parse($_GET['periode'])->format('Y').'001';
//            $test = $_GET['periode'];
            $periode = $_GET['periode'];
//            dd($_GET['periode']);
//            $per_akhir = date('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
            $per_akhir = Carbon::parse($_GET['periode'])->format('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_awal = date('Y').'001';
            $per_akhir = date('Y').'0'.date('m');
            $periode = date('Y-m');
        }

        $departemen = Departemen::where('kepala_departemen', Auth::id())->first();
        $allowances = DepartmentExpense::where('wilayah_id', Auth::user()->wilayah_id)->get();
//        dd(Auth::user()->wilayah_id);
        $data_allowance = [];
        foreach ($allowances as $index=> $item){
            $keuangan = $this->get_keuangan($per_awal, $per_akhir, $item['id']);
            $data_allowance[$index]['id'] = $item['id'];
            $data_allowance[$index]['account_name'] = $item['nama'];
            $data_allowance[$index]['account_code'] = $item['account_code'];
            $data_allowance[$index]['budget'] = $keuangan['travel_budget'];
            $data_allowance[$index]['actual'] = $keuangan['travel_actual'];
            $data_allowance[$index]['travel_advance'] = $keuangan['travel_advance'];
            $data_allowance[$index]['sisa'] = $keuangan['sisa_travel'];
        }
//        dd($travel);
        return view('departemen.Keuangan.index', compact('departemen', 'data_allowance', 'periode'));
    }

    public function detail_keuangan ($id) {
        if (isset($_GET['periode'])){
            $per_awal = Carbon::parse($_GET['periode'])->format('Y').'001';
//            $test = $_GET['periode'];
            $periode = $_GET['periode'];
            $per_akhir = Carbon::parse($_GET['periode'])->format('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_awal = date('Y').'001';
            $per_akhir = date('Y').'0'.date('m');
            $periode = date('Y-m');
        }
        $detail = $this->get_detail_keuangan($per_awal, $per_akhir, $id);
        $temp = $this->get_keuangan($per_awal, $per_akhir, $id);
        $saldo = $temp['travel_actual'];

        return view('departemen.Keuangan.detail', compact('per_akhir', 'per_awal', 'periode', 'detail', 'saldo'));
    }
}
