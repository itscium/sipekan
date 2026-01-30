<?php

namespace App\Http\Controllers\Report\Departemen\Expense;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Wilayah;
use App\Models\WIUM\A_SALFLDG;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StrategicController extends Controller
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

    function get_keuangan ($per_awal, $per_akhir, $id){
        $wilayah_id = Auth::user()->wilayah_id;
//        $table = $this->table($wilayah_id);
        $table_a = $this->table_a($wilayah_id);
        $table_b = $this->table_b($wilayah_id);

        $departemen = Departemen::where('id', $id)->first();
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

        //change strategic plan
        $strategic_actual = number_format($strategic_actual*-1);
        $strategic_budget = number_format($strategic_budget*-1);
        $sisa_strategic = number_format($sisa_strategic*-1);

        return [
            'travel_budget' => $strategic_budget,
            'travel_actual' => $strategic_actual,
            'sisa_travel' => $sisa_strategic
        ];
    }

    function get_detail_keuangan ($per_awal, $per_akhir, $jenis, $id){
        $wilayah_id = Auth::user()->wilayah_id;
        $table = $this->table_a($wilayah_id);

        $departemen = Departemen::where('id', $id)->first();
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

    public function index (){
        if (isset($_GET['periode'])){
//            $test = $_GET['periode'];
            $per_awal = Carbon::parse($_GET['periode'])->format('Y').'001';
            $periode = $_GET['periode'];
            $per_akhir = Carbon::parse($_GET['periode'])->format('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_awal = date('Y').'001';
            $per_akhir = date('Y').'0'.date('m');
            $periode = date('Y-m');
        }
        $departemens = Departemen::where('wilayah_id', Auth::user()->wilayah_id)->get();
//dd($departemens);
        $data = [];
        foreach ($departemens as $index=> $item){
            $keuangan = $this->get_keuangan($per_awal, $per_akhir, $departemens[$index]['id']);
            $data[$index]['id'] = $departemens[$index]['id'];
            $data[$index]['nama_departemen'] = $departemens[$index]['nama_departemen'];
            $data[$index]['budget'] = $keuangan['travel_budget'];
            $data[$index]['actual'] = $keuangan['travel_actual'];
            $data[$index]['sisa'] = $keuangan['sisa_travel'];
        }
        $wilayah = Wilayah::where('id', Auth::user()->wilayah_id)->first();
        return view('report.departemen.strategic-plan.index', compact('departemens', 'wilayah', 'data', 'per_awal', 'per_akhir', 'periode'));
    }

    public function detail_strategic ($id_departemen){
        $jenis = 'strategic';

        if (isset($_GET['periode'])){
//            $test = $_GET['periode'];
            $per_awal = Carbon::parse($_GET['periode'])->format('Y').'001';
            $periode = $_GET['periode'];
            $per_akhir = Carbon::parse($_GET['periode'])->format('Y').'0'.Carbon::parse($_GET['periode'])->format('m');
//            dd($per_akhir);
        }else{
            $per_awal = date('Y').'001';
            $per_akhir = date('Y').'0'.date('m');
            $periode = date('Y-m');
        }
        $detail = $this->get_detail_keuangan($per_awal, $per_akhir, $jenis, $id_departemen);
        $temp = $this->get_keuangan($per_awal, $per_akhir, $id_departemen);

        $departemen = Departemen::where('id', $id_departemen)->first();
//        dd($departemen)
        $saldo = $temp['travel_actual'];

//        $saldo = match ($jenis) {
//            'travel' => $temp['travel_actual'],
//            'special' => $temp['special_travel_actual'],
//            'strategic' => $temp['strategic_actual'],
//            'office' => $temp['office_actual'],
//            default => '',
//        };
//        dd($departemen)

        return view('report.departemen.strategic-plan.detail', compact('per_akhir', 'per_awal', 'periode', 'detail', 'saldo', 'departemen'));
    }
}
