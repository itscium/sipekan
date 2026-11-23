<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\FinanceLock;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Halaman Beranda.
     */
    public function index(Request $request, DashboardService $dashboard)
    {
        $user = Auth::user();
        // Laporan keuangan Business Unit ini ditutup (Admin > Akses keuangan): jangan muat data keuangan sama sekali
        $locked = FinanceLock::lockedFor($user);

        $d = $dashboard->forUser($user);
        if ($locked) {
            $d['personal'] = $d['travel'] = $d['department'] = null;
        }

        return view('home', [
            'user' => $user,
            'd' => $d,
            // Filter bulan gaji: ?gaji=YYYY-MM
            'pay' => $locked ? ['available' => false, 'error' => false] : $dashboard->payroll($user, $request->query('gaji')),
            'financeLocked' => $locked,
            'announcements' => Announcement::activeFor($user),
        ]);
    }
}
