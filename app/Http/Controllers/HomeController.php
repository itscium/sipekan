<?php

namespace App\Http\Controllers;

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

        return view('home', [
            'user' => $user,
            'd' => $dashboard->forUser($user),
            // Filter bulan gaji: ?gaji=YYYY-MM
            'pay' => $dashboard->payroll($user, $request->query('gaji')),
        ]);
    }
}
