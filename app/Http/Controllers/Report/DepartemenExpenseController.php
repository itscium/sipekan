<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\DepartmentExpense;
use App\Models\Wilayah;
use App\Models\WIUM\A_SALFLDG;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DepartemenExpenseController extends Controller
{
    public function __construct (){
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

    function get_keuangan ($per_awal, $per_akhir, $id_allowance, $id_department){
        $wilayah_id = Auth::user()->wilayah_id;
        $table_a = $this->table_a($wilayah_id);
        $table_b = $this->table_b($wilayah_id);
        $expense = DepartmentExpense::where('id', $id_allowance)->first();
        $sisa_travel = 0;
        $travel_advance = 0;
//        dd($expense->account_code);

        $departemen = Departemen::where('id', $id_department)->first();
        //get Travel Expense
        $travel_actual = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $expense->account_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, $per_akhir])
            ->sum($table_a.'.AMOUNT');
        $travel_budget = (new A_SALFLDG)->setTable($table_b)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $expense->account_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, $per_akhir])
            ->sum($table_b.'.AMOUNT');
        if (!empty($departemen->user->travel_account)){
            $travel_advance = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
                ->where('ACCNT_CODE', $departemen->user->travel_account)
                ->where('PERIOD', '<=', $per_akhir)
                ->sum($table_a.'.AMOUNT');
        }
        if ($expense->account_code === '822110'){
            $temp = $travel_advance + $travel_actual;
            $sisa_travel = $travel_budget - $temp;
        }else{
            $sisa_travel = $travel_budget - $travel_actual;
        }
//        $temp = $travel_advance + $travel_actual;
//        $sisa_travel = $travel_budget - $temp;

        //change into number format and remove (-)
        $travel_actual = number_format($travel_actual*-1);
        $travel_budget = number_format($travel_budget*-1);
        $sisa_travel = number_format($sisa_travel*-1);
        $travel_advance = number_format($travel_advance*-1);

        return [
            'budget' => $travel_budget,
            'actual' => $travel_actual,
            'sisa' => $sisa_travel,
            'travel_advance' => $travel_advance,
        ];
    }

    function get_detail_keuangan ($per_awal, $per_akhir, $jenis, $id){
        $wilayah_id = Auth::user()->wilayah_id;
        $table_a = $this->table_a($wilayah_id);

        $departemen = Departemen::where('id', $id)->first();
        $allowance = DepartmentExpense::where('id', $jenis)->first();
//        $code = '';
//        dd($per_awal, $per_akhir);
        $detail = [];
        $keuangan = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $allowance->account_code)
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

    public function index () {
        $departemens = Departemen::all();
        $expense = DepartmentExpense::where('wilayah_id', Auth::user()->wilayah_id)->get();
        $wilayah = Wilayah::where('id', Auth::user()->wilayah_id)->first();
        return view('report.index', compact('departemens', 'wilayah', 'expense'));
    }

    public function show ($id){
        if (isset($_GET['periode'])){
//            $test = $_GET['periode'];
            $per_awal = Carbon::parse($_GET['periode'])->format('Y').'001';
            $periode = $_GET['periode'];
            $per_akhir = date('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_awal = date('Y').'001';
            $per_akhir = date('Y').'0'.date('m');
            $periode = date('Y-m');
        }

        $departemen = Departemen::where('wilayah_id', Auth::user()->wilayah_id)->get();
        $allowance = DepartmentExpense::find($id);
//        dd($departemen);
        $data_report = [];
        foreach ($departemen as $index=>$item){
            $keuangan = $this->get_keuangan($per_awal, $per_akhir, $id, $item['id']);
            $data_report[$index]['id'] = $item['id'];
            $data_report[$index]['nama_departemen'] = $item['nama_departemen'];
            $data_report[$index]['budget'] = $keuangan['budget'];
            $data_report[$index]['actual'] = $keuangan['actual'];
            $data_report[$index]['travel_advance'] = $keuangan['travel_advance'];
            $data_report[$index]['sisa'] = $keuangan['sisa'];
        }
//        $keuangan = $this->get_keuangan($per_awal, $per_akhir, $id);
        return view('report.show', compact('departemen', 'periode', 'data_report', 'allowance'));
    }

    public function details($jenis, $id_departemen, $per){
//        dd($per);

//        $per_awal = date('Y').'001';

//        dd($per_awal);
        if (isset($_GET['periode'])){
//            $test = $_GET['periode'];
            $periode = $_GET['periode'];
            $per_awal = Carbon::parse($_GET['periode'])->format('Y').'001';
            $per_akhir = Carbon::parse($_GET['periode'])->format('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_awal = Carbon::parse($per)->format('Y').'001';
            $per_akhir = Carbon::parse($per)->format('Y').'0'.Carbon::parse($per)->format('m');
            $periode = Carbon::parse($per)->format('Y-m');
        }
//        dd($jenis);
        $detail = $this->get_detail_keuangan($per_awal, $per_akhir, $jenis, $id_departemen);
        $temp = $this->get_keuangan($per_awal, $per_akhir, $jenis, $id_departemen);

        $departemen = Departemen::where('id', $id_departemen)->first();
//        dd($temp);
        $saldo = $temp['actual'];
//        dd($departemen)

        return view('report.details', compact('per_akhir', 'per_awal', 'periode', 'detail', 'saldo', 'departemen', 'jenis'));
    }
}
