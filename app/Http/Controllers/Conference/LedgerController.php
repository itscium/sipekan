<?php

namespace App\Http\Controllers\Conference;

use App\Http\Controllers\Controller;
use App\Models\WIUM\A_SALFLDG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LedgerController extends Controller
{
    public function __construct (){
        $this->middleware('auth');
    }
    function get_detail_keuangan ($account, $tgl_awal, $tgl_akhir)
    {
        $wilayah_id = Auth::user()->wilayah_id;
//        $table = $this->table($wilayah_id);
        $table = 'dbo.ADV_A_SALFLDG';
//        dd($table);
        $saldo_akhir ='';
        $list_keuangan = [];
        $keuangan = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')->where('ACCNT_CODE', $account)->whereBetween('TRANS_DATETIME', [$tgl_awal,$tgl_akhir])->orderBy('TRANS_DATETIME')->get();
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
        $account = Auth::user()->wilayah->account_on_wium;
        [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan] = $this->get_detail_keuangan($account, $tgl_awal, $tgl_akhir);
        return view('ledgers.conference-on-wium', compact('keuangan', 'saldo_akhir', 'list_keuangan', 'saldo_awal', 'tgl_akhir', 'tgl_awal'));
    }
}
