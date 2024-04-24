<?php

namespace App\Http\Controllers\Conference;

use App\Http\Controllers\Controller;
use App\Models\WIUM\A_SALFLDG;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $keuangan = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $account)->whereBetween('PERIOD', [$tgl_awal,$tgl_akhir])->orderBy('PERIOD')->orderBy('JRNAL_NO')->orderBy('JRNAL_LINE')->get();
        $balance = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')
            ->where('ACCNT_CODE', $account)->where('PERIOD', '<', $tgl_awal)->sum($table.'.AMOUNT');
        $opening_balance = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')->where('ACCNT_CODE', $account)->where('PERIOD', '<', $tgl_awal)->sum($table.'.AMOUNT');
        $saldo_awal = $this->number_to_credit(-$opening_balance);
//        dd($keuangan);
//        $group_keuangan = $this->group_by('PERIOD', $keuangan);
//        dd($group_keuangan);


        foreach ($keuangan as $index => $item) {
            $list_keuangan[$index]['credit'] = '';
            $list_keuangan[$index]['debit'] = '';
            $list_keuangan[$index]['balance'] = $balance;
            $list_keuangan[$index]['tanggal'] = date('M d, Y', strtotime($item['TRANS_DATETIME']));
            $list_keuangan[$index]['description'] = $item['DESCRIPTN'];
            $list_keuangan[$index]['reference'] = $item['TREFERENCE'];
            $list_keuangan[$index]['nomor_jurnal'] = $item['JRNAL_NO'];
            $list_keuangan[$index]['period'] = $item['PERIOD'];
            $list_keuangan[$index]['journal_line'] = $item['JRNAL_LINE'];
            if ($item['D_C'] === 'D') {
                $list_keuangan[$index]['debit'] = number_format($item['AMOUNT'] * -1);
                $balance += $item['AMOUNT'];
            } else {
                $list_keuangan[$index]['credit'] = number_format($item['AMOUNT']);
                $balance += $item['AMOUNT'];
            }
            $list_keuangan[$index]['balance'] = $this->number_to_credit(-$balance);
            $saldo = (new A_SALFLDG)->setTable($table)->where('ALLOCATION', '<>', 'C')->where('ACCNT_CODE', $account)->where('PERIOD', '<=', $tgl_akhir)->sum($table.'.AMOUNT');
            $saldo_akhir = $this->number_to_credit(-$saldo);
        }
//        $list_keuangan_fix = $this->group_by('period', $list_keuangan);
        $list_keuangan_fix = collect($list_keuangan)->groupBy('period');
        return [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan, $list_keuangan_fix];
    }

    /**
     * Function that groups an array of associative arrays by some key.
     *
     * @param {String} $key Property to sort by.
     * @param {Array} $data Array that stores multiple associative arrays.
     */
    function group_by($key, $data) {
        $result = array();

        foreach($data as $val) {
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                $result[""][] = $val;
            }
        }

        return $result;
    }

    function number_to_credit($number) {
        if ($number < 0) {
            return (number_format($number*-1))." Cr.";
        }

        return number_format($number);
    }

    public function index()
    {
        if (isset($_GET['periode_awal']) || isset($_GET['periode_akhir'])) {
            $awal_period = Carbon::parse($_GET['periode_awal'])->format('Y').'0'.Carbon::parse($_GET['periode_awal'])->format('m');
            $akhir_period = Carbon::parse($_GET['periode_akhir'])->format('Y').'0'.Carbon::parse($_GET['periode_akhir'])->format('m');
            $periode_awal = $_GET['periode_awal'];
            $periode_akhir = $_GET['periode_akhir'];
        }else{
            $awal_period = date('Y').'0'.date('m');
            $akhir_period = date('Y').'0'.date('m');
            $periode_awal = date('Y-m');
//        dd(date('Y-m-d', strtotime('-1 day', strtotime($tgl_awal))));
            $periode_akhir = date('Y-m');
        }
//        dd($awal_period);
//        dd(Auth::user()->wilayah_id);
        $account = Auth::user()->wilayah->account_on_wium;
        [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan, $list_keuangan_fix] = $this->get_detail_keuangan($account, $awal_period, $akhir_period);
//        dd($list_keuangan_fix);
        if ((isset($_GET['print']))){
            return $this->print_ledger($periode_awal, $periode_akhir, $account, $awal_period, $akhir_period);
        }
        if ((isset($_GET['pdf']))){
            return $this->export_pdf($periode_awal, $periode_akhir, $account, $awal_period, $akhir_period);
        }
        return view('ledgers.conference-on-wium', compact('keuangan', 'saldo_akhir', 'list_keuangan', 'list_keuangan_fix', 'saldo_awal', 'periode_akhir', 'periode_awal'));
    }

    public function print_ledger($periode_awal, $periode_akhir, $account, $awal_period, $akhir_period)
    {
//        dd($awal_period);
        [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan, $list_keuangan_fix] = $this->get_detail_keuangan($account, $awal_period, $akhir_period);
        return view('ledgers.print.print-ledger', compact('keuangan', 'saldo_akhir', 'list_keuangan', 'list_keuangan_fix', 'saldo_awal', 'periode_akhir', 'periode_awal'));
    }

    public function export_pdf($periode_awal, $periode_akhir, $account, $awal_period, $akhir_period)
    {

        [$saldo_awal, $saldo_akhir, $list_keuangan, $keuangan, $list_keuangan_fix] = $this->get_detail_keuangan($account, $awal_period, $akhir_period);
        return view('ledgers.print.pdf-ledger', compact('keuangan', 'saldo_akhir', 'list_keuangan', 'list_keuangan_fix', 'saldo_awal', 'periode_akhir', 'periode_awal'));
    }
}
