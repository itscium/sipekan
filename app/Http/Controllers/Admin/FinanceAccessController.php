<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceLock;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\ActivityTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Menu Admin > Akses keuangan: tutup/buka laporan keuangan per Business Unit. */
class FinanceAccessController extends Controller
{
    public function __construct(private ActivityTracker $tracker)
    {
        $this->middleware(['auth', 'can:administrator']);
    }

    public function index()
    {
        $admin = Auth::user();
        $managed = $admin->managedWilayahIds();
        $counts = User::where('type', 'user')->where('status', 1)
            ->selectRaw('wilayah_id, COUNT(*) AS n')->groupBy('wilayah_id')->pluck('n', 'wilayah_id');

        return view('admin.finance-access.index', [
            // Admin wilayah hanya melihat Business Unit yang ia kelola
            'wilayah' => Wilayah::when($managed !== null, fn ($q) => $q->whereIn('id', $managed ?: [0]))
                ->orderBy('id')->get(['id', 'nama', 'kode', 'english_name']),
            'locks' => FinanceLock::with('locker:id,name')->get()->keyBy('wilayah_id'),
            'userCounts' => $counts,
        ]);
    }

    public function lock(Request $request, Wilayah $wilayah)
    {
        abort_unless(Auth::user()->managesWilayah($wilayah->id), 403, 'Wilayah ini di luar wewenang Anda.');
        $request->validate(['konfirmasi' => ['accepted']], ['konfirmasi.accepted' => 'Centang konfirmasi terlebih dahulu.']);
        FinanceLock::firstOrCreate(['wilayah_id' => $wilayah->id], ['locked_by' => Auth::id()]);
        FinanceLock::forgetCache();
        $this->tracker->adminAction(Auth::user(), 'lock', 'menutup laporan keuangan ' . $wilayah->nama . ' (' . $wilayah->short_code . ')', $request);
        alert()->success('Akses ditutup', 'Laporan keuangan ' . $wilayah->nama . ' tidak bisa dibuka lagi oleh pegawai.');
        return redirect()->route('admin.finance-access.index');
    }

    public function unlock(Request $request, Wilayah $wilayah)
    {
        abort_unless(Auth::user()->managesWilayah($wilayah->id), 403, 'Wilayah ini di luar wewenang Anda.');
        FinanceLock::where('wilayah_id', $wilayah->id)->delete();
        FinanceLock::forgetCache();
        $this->tracker->adminAction(Auth::user(), 'unlock', 'membuka kembali laporan keuangan ' . $wilayah->nama . ' (' . $wilayah->short_code . ')', $request);
        alert()->success('Akses dibuka', 'Laporan keuangan ' . $wilayah->nama . ' bisa dibuka kembali.');
        return redirect()->route('admin.finance-access.index');
    }
}
