<?php

namespace App\Http\Controllers\Personal;

use App\Http\Controllers\Controller;
use App\Models\WIUM\A_SALFLDG;
use App\Models\WIUM\Payrol;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class KeuanganController extends Controller
{
    public function __construct (){
        $this->middleware('auth');
    }
    function table ($wilayah_id){
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
    function get_detail_keuangan ($account, $tgl_awal, $tgl_akhir)
    {
        $wilayah_id = Auth::user()->wilayah_id;
        $table = $this->table($wilayah_id);
//        dd($table);
        $saldo_akhir ='';
        $list_keuangan = [];
        $keuangan = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')->where('ACCNT_CODE', $account)->whereBetween('TRANS_DATETIME', [$tgl_awal,$tgl_akhir])->get();
        $opening_balance = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')->where('ACCNT_CODE', $account)->where('TRANS_DATETIME', '<=', date('Y-m-d', strtotime('-1 day', strtotime($tgl_awal))))->sum($table.'.AMOUNT');
        $balance = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')->where('ACCNT_CODE', $account)->where('TRANS_DATETIME', '<', $tgl_awal)->sum($table.'.AMOUNT');
        $saldo_awal = $this->number_to_credit(-$opening_balance);


        foreach ($keuangan as $index => $item) {
            $list_keuangan[$index]['credit'] = '';
            $list_keuangan[$index]['debit'] = '';
            $list_keuangan[$index]['balance'] = $balance;
            $list_keuangan[$index]['tanggal'] = date('M d, Y', strtotime($item['TRANS_DATETIME']));
            $list_keuangan[$index]['description'] = $item['DESCRIPTN'];
            $list_keuangan[$index]['nomor_jurnal'] = $item['JRNAL_NO'];
            if ($item['D_C'] === 'D') {
                $list_keuangan[$index]['debit'] = number_format($item['AMOUNT'] * -1);
                $balance += $item['AMOUNT'];
            } else {
                $list_keuangan[$index]['credit'] = number_format($item['AMOUNT']);
                $balance += $item['AMOUNT'];
            }
            $list_keuangan[$index]['balance'] = $this->number_to_credit(-$balance);
            $saldo = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')->where('ACCNT_CODE', $account)->where('TRANS_DATETIME', '<=', date($tgl_akhir))->sum($table.'.AMOUNT');
            $saldo_akhir = $this->number_to_credit(-$saldo);
        }
            return [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan];
    }

    function number_to_credit($number) {
        if ($number < 0) {
            return (number_format($number*-1))." Cr.";
        }

        return number_format($number);
    }

    public function index()
    {
        if (isset($_GET['tgl_awal']) || isset($_GET['tgl_akhir'])) {
            $tgl_awal = $_GET['tgl_awal'];
            $tgl_akhir = $_GET['tgl_akhir'];
        }else{
            $tgl_awal = date('Y-m-01');
//        dd(date('Y-m-d', strtotime('-1 day', strtotime($tgl_awal))));
            $tgl_akhir = date('Y-m-t');
        }
//        dd(Auth::user()->wilayah_id);
        $account = Auth::user()->ACCNT_CODE;
        [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan] = $this->get_detail_keuangan($account, $tgl_awal, $tgl_akhir);
        return view('personal.keuangan.index', compact('keuangan', 'saldo_akhir', 'list_keuangan', 'saldo_awal', 'tgl_akhir', 'tgl_awal'));
    }

    public function travel(){
        if (isset($_GET['tgl_awal']) || isset($_GET['tgl_akhir'])) {
            $tgl_awal = $_GET['tgl_awal'];
            $tgl_akhir = $_GET['tgl_akhir'];
        }else{
            $tgl_awal = date('Y-m-01');
//        dd(date('Y-m-d', strtotime('-1 day', strtotime($tgl_awal))));
            $tgl_akhir = date('Y-m-t');
        }
        $account_travel = Auth::user()->travel_account;
        [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan] = $this->get_detail_keuangan($account_travel, $tgl_awal, $tgl_akhir);
        return view('personal.keuangan.travel', compact('keuangan', 'saldo_akhir', 'list_keuangan', 'saldo_awal', 'tgl_akhir', 'tgl_awal'));
    }

    public function payrol(){

//        $period = '';
        if (isset($_GET['periode'])){
            $per_akhir = Carbon::parse($_GET['periode'])->format('m');
            $year = Carbon::parse($_GET['periode'])->format('Y');
            $period = Carbon::parse($_GET['periode'])->format('Y-m');
        }else{
            $per_akhir = date('m');
            $period = date('Y-m');
            $year = date('Y');
        }
//        $period = $_GET['periode'] ?? date('m');
//        dd($period);
//        dd($_GET['periode']);
        $payrol = Payrol::where('enrollment_code', Auth::user()->ACCNT_CODE)->where('period', ltrim($per_akhir))->where('year', $year)->where('stub', 1)->get();
        $net = Payrol::where('enrollment_code', Auth::user()->ACCNT_CODE)->where('period', ltrim($per_akhir))->where('year', $year)->where('stub', 0)->first();
        return view('personal.keuangan.payrol', compact('payrol', 'period', 'net'));
    }

    public function payrol_pdf($tgl){

            $per_akhir = Carbon::parse($tgl)->format('m');
            $year = Carbon::parse($tgl)->format('Y');
//            $period = Carbon::parse($_GET['periode'])->format('Y-m');

//        dd($per_akhir);

        $profile = Payrol::where('enrollment_code', Auth::user()->ACCNT_CODE)->where('period', ltrim($per_akhir))->where('year', $year)->where('stub', 0)->first();
        $earning = Payrol::where('enrollment_code', Auth::user()->ACCNT_CODE)->where('signal', '+')->where('period', ltrim($per_akhir))->where('year', $year)->where('stub', 1)->get();
        $deduction = Payrol::where('enrollment_code', Auth::user()->ACCNT_CODE)->where('signal', '-')->where('period', ltrim($per_akhir))->where('year', $year)->where('stub', 1)->get();
//        dd($earning);
        return view('personal.keuangan.wium.pdf-payrol', compact('earning', 'deduction', 'profile'));
//        $pdf = PDF::loadView('personal.keuangan.wium.pdf-payrol')
//            ->setPaper('a4');
//
//        return $pdf->stream();
    }
}
