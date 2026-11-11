<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
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
    public function index(DashboardService $dashboard)
    {
        $user = Auth::user();

        return view('home', [
            'user' => $user,
            'd' => $dashboard->forUser($user),
        ]);
    }
}
