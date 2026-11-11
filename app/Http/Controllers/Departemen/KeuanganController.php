<?php

namespace App\Http\Controllers\Departemen;

use App\Http\Controllers\Controller;
use App\Models\Allowances;
use App\Models\Departemen;
use App\Models\DepartmentExpense;
use App\Models\Wilayah;
use App\Models\WIUM\A_SALFLDG;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

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
            case 20003:
                $table = 'dbo.WIU_A_SALFLDG';
                break;  
        }
        return $table;
    }
    function table_b ($wilayah_id){
        $table = '';
        switch ($wilayah_id){
            case 1:
                $table = 'dbo.CIU_B_SALFLDG';
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
            case 20003:
                $table = 'dbo.WIU_B_SALFLDG';
                break;  
        }
        return $table;
    }

    function get_keuangan ($per_awal, $per_akhir, $id_allowance, Departemen $departemen){
        // Pakai wilayah departemen (bukan wilayah user) supaya admin juga bisa melihat.
        $wilayah_id = $departemen->wilayah_id;
        $table_a = $this->table_a($wilayah_id);
        $table_b = $this->table_b($wilayah_id);
        $allowance = DepartmentExpense::where('id', $id_allowance)->first();
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
            ->where('ACCNT_CODE', optional($departemen->user)->travel_account ?? '')
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

    function get_detail_keuangan ($per_awal, $per_akhir, $id_allowance, Departemen $departemen){
        $wilayah_id = $departemen->wilayah_id;
        $table = $this->table_a($wilayah_id);
        // $code = '';
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

    /**
     * Departemen yang ditampilkan:
     * - kepala departemen: departemen yang ia pimpin;
     * - admin: departemen yang dipilih lewat ?departemen=ID.
     * Mengembalikan null jika tidak ada.
     */
    private function resolveDepartemen(Request $request): ?Departemen
    {
        if (Gate::allows('administrator') && $request->filled('departemen')) {
            // Departemen dari wilayah yang disembunyikan tidak boleh dibuka admin
            return Departemen::where('id', $request->query('departemen'))->budgetVisible()->first();
        }
        // Kepala departemen di wilayah yang disembunyikan juga tidak melihat anggarannya
        return Departemen::where('kepala_departemen', Auth::id())->budgetVisible()->first();
    }

    /** Wilayah yang anggaran departemennya disembunyikan dari pilihan admin (config/sipekan.php). */
    private function hiddenWilayah(): array
    {
        return array_map('strval', config('sipekan.hidden_budget_wilayah_ids', []));
    }

    /** Periode SunSystems dari ?periode=YYYY-MM: Januari s.d. bulan terpilih. */
    private function periode(Request $request): array
    {
        $p = $request->query('periode');
        $date = ($p && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $p)) ? Carbon::createFromFormat('Y-m', $p) : now();
        return [$date->format('Y') . '001', $date->format('Y') . '0' . $date->format('m'), $date->format('Y-m')];
    }

    public function index(Request $request){
        [$per_awal, $per_akhir, $periode] = $this->periode($request);

        $isAdmin = Gate::allows('administrator');
        $departemen = $this->resolveDepartemen($request);
        // Kepala departemen yang wilayahnya disembunyikan: tampilkan pesan khusus, bukan "belum terdaftar"
        $anggaran_disembunyikan = !$isAdmin && !$departemen
            && Departemen::where('kepala_departemen', Auth::id())->exists();

        // Admin memilih wilayah dulu (bawaan: WIUM), lalu departemen di wilayah itu.
        $daftar_wilayah = collect();
        $daftar_departemen = collect();
        $wilayah_id = null;
        if ($isAdmin) {
            $daftar_wilayah = Wilayah::whereNotIn('id', $this->hiddenWilayah())
                ->orderBy('id')->get(['id', 'nama', 'english_name']);
            $wilayah_id = $departemen->wilayah_id ?? $request->query('wilayah');
            // Wilayah tersembunyi atau tidak dikenal -> kembali ke wilayah bawaan (WIUM)
            if (!$daftar_wilayah->contains('id', $wilayah_id)) {
                $wilayah_id = config('sipekan.default_wilayah_id');
            }
            $daftar_departemen = Departemen::where('wilayah_id', $wilayah_id)
                ->orderBy('nama_departemen')->get(['id', 'nama_departemen', 'wilayah_id']);
        }

        $data_allowance = [];
        if ($departemen) {
            $allowances = DepartmentExpense::where('wilayah_id', $departemen->wilayah_id)->get();
            foreach ($allowances as $index => $item) {
                $keuangan = $this->get_keuangan($per_awal, $per_akhir, $item['id'], $departemen);
                $data_allowance[$index]['id'] = $item['id'];
                $data_allowance[$index]['account_name'] = $item['nama'];
                $data_allowance[$index]['account_code'] = $item['account_code'];
                $data_allowance[$index]['budget'] = $keuangan['travel_budget'];
                $data_allowance[$index]['actual'] = $keuangan['travel_actual'];
                $data_allowance[$index]['travel_advance'] = $keuangan['travel_advance'];
                $data_allowance[$index]['sisa'] = $keuangan['sisa_travel'];
            }
        }

        return view('departemen.Keuangan.index', compact('departemen', 'data_allowance', 'periode', 'isAdmin', 'daftar_departemen', 'daftar_wilayah', 'wilayah_id', 'anggaran_disembunyikan'));
    }

    public function detail_keuangan (Request $request, $id) {
        [$per_awal, $per_akhir, $periode] = $this->periode($request);

        $departemen = $this->resolveDepartemen($request);
        $allowance = DepartmentExpense::find($id);
        if (!$departemen || !$allowance || (string) $allowance->wilayah_id !== (string) $departemen->wilayah_id) {
            return redirect()->route('departemen.keuangan.index', $request->only('departemen', 'periode'));
        }

        $detail = $this->get_detail_keuangan($per_awal, $per_akhir, $id, $departemen);
        $temp = $this->get_keuangan($per_awal, $per_akhir, $id, $departemen);
        $saldo = $temp['travel_actual'];

        return view('departemen.Keuangan.detail', compact('per_akhir', 'per_awal', 'periode', 'detail', 'saldo', 'departemen', 'allowance'));
    }
}
