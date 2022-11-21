<?php

namespace App\Http\Controllers\Departemen;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
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
                $table = 'dbo.ADV_A_SALFLDG';
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
        }
        return $table;
    }

    function get_keuangan ($per_awal, $per_akhir){
        $wilayah_id = Auth::user()->wilayah_id;
        $table_a = $this->table_a($wilayah_id);
        $table_b = $this->table_b($wilayah_id);

        $departemen = Departemen::where('kepala_departemen', Auth::id())->first();
        //get Travel Expense
        $travel_actual = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->travel_expense_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, $per_akhir])
            ->sum($table_a.'.AMOUNT');
        $travel_budget = (new A_SALFLDG)->setTable($table_b)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->travel_expense_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, date('Y'.'012')])
            ->sum($table_b.'.AMOUNT');
        $travel_advance = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->user->travel_account)
            ->where('TRANS_DATETIME', '<=', date('Y-m-d'))
            ->sum($table_a.'.AMOUNT');

        $temp = $travel_advance + $travel_actual;
        $sisa_travel = $travel_budget - $temp;

        //get Special Travel
        $special_travel_actual = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->travel_special_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, $per_akhir])
            ->sum($table_a.'.AMOUNT');
        $special_travel_budget = (new A_SALFLDG)->setTable($table_b)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->travel_special_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, date('Y'.'012')])
            ->sum($table_b.'.AMOUNT');
        $sisa_special_travel = $special_travel_budget - $special_travel_actual;

        //get Strategic Plan
        $strategic_actual = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->strategic_plan_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, $per_akhir])
            ->sum($table_a.'.AMOUNT');
        $strategic_budget = (new A_SALFLDG)->setTable($table_b)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->strategic_plan_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, date('Y'.'012')])
            ->sum($table_b.'.AMOUNT');
        $sisa_strategic = $strategic_budget - $strategic_actual;

        //get Office Expense
        $office_actual = (new A_SALFLDG)->setTable($table_a)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->office_expense_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, $per_akhir])
            ->sum($table_a.'.AMOUNT');
        $office_budget = (new A_SALFLDG)->setTable($table_b)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $departemen->office_expense_code)
            ->where('ANAL_T3', $departemen->department_code)
            ->whereBetween('PERIOD', [$per_awal, date('Y'.'012')])
            ->sum($table_b.'.AMOUNT');
        $sisa_office = $office_budget - $office_actual;

        //change into number format and remove (-)
        $travel_actual = number_format($travel_actual*-1);
        $travel_budget = number_format($travel_budget*-1);
        $sisa_travel = number_format($sisa_travel*-1);
        $travel_advance = number_format($travel_advance*-1);

        //change special travel
        $special_travel_actual = number_format($special_travel_actual*-1);
        $special_travel_budget = number_format($special_travel_budget*-1);
        $sisa_special_travel = number_format($sisa_special_travel*-1);

        //change strategic plan
        $strategic_actual = number_format($strategic_actual*-1);
        $strategic_budget = number_format($strategic_budget*-1);
        $sisa_strategic = number_format($sisa_strategic*-1);

        //change office expense
        $office_actual = number_format($office_actual*-1);
        $office_budget = number_format($office_budget*-1);
        $sisa_office = number_format($sisa_office*-1);

        return [
            'travel_budget' => $travel_budget,
            'travel_actual' => $travel_actual,
            'sisa_travel' => $sisa_travel,
            'special_travel_budget' => $special_travel_budget,
            'special_travel_actual' => $special_travel_actual,
            'sisa_special_travel' => $sisa_special_travel,
            'strategic_budget' => $strategic_budget,
            'strategic_actual' => $strategic_actual,
            'sisa_strategic' => $sisa_strategic,
            'office_budget' => $office_budget,
            'office_actual' => $office_actual,
            'sisa_office' => $sisa_office,
            'travel_advance' => $travel_advance,
        ];
    }

    function get_detail_keuangan ($per_awal, $per_akhir, $jenis){
        $wilayah_id = Auth::user()->wilayah_id;
        $table = $this->table_a($wilayah_id);

        $departemen = Departemen::where('kepala_departemen', Auth::id())->first();
//        $code = '';
        $code = match ($jenis) {
            'travel' => $departemen->travel_expense_code,
            'special' => $departemen->travel_special_code,
            'strategic' => $departemen->strategic_plan_code,
            'office' => $departemen->office_expense_code,
            default => '',
        };
        $detail = [];
        $keuangan = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $code)
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
        $per_awal = date('Y').'001';
        if (isset($_GET['periode'])){
//            $test = $_GET['periode'];
            $periode = $_GET['periode'];
            $per_akhir = date('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_akhir = date('Y').'0'.date('m');
            $periode = date('Y-m');
        }



        $departemen = Departemen::where('kepala_departemen', Auth::id())->first();
        $keuangan = $this->get_keuangan($per_awal, $per_akhir);
//        dd($travel);
        return view('departemen.Keuangan.index', compact('departemen', 'keuangan', 'periode'));
    }

    public function detail_keuangan ($jenis) {
        $per_awal = date('Y').'001';
        if (isset($_GET['periode'])){
//            $test = $_GET['periode'];
            $periode = $_GET['periode'];
            $per_akhir = date('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_akhir = date('Y').'0'.date('m');
            $periode = date('Y-m');
        }
        $detail = $this->get_detail_keuangan($per_awal, $per_akhir, $jenis);
        $temp = $this->get_keuangan($per_awal, $per_akhir);
        $saldo = match ($jenis) {
            'travel' => $temp['travel_actual'],
            'special' => $temp['special_travel_actual'],
            'strategic' => $temp['strategic_actual'],
            'office' => $temp['office_actual'],
            default => '',
        };

        return view('departemen.Keuangan.detail', compact('per_akhir', 'per_awal', 'periode', 'detail', 'saldo'));
    }
}
