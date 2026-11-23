<?php

namespace App\Http\Middleware;

use App\Models\FinanceLock;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Tolak halaman laporan keuangan jika Business Unit pengguna sudah ditutup
 * (Admin > Akses keuangan). Admin tidak terpengaruh.
 */
class EnsureFinanceOpen
{
    public function handle(Request $request, Closure $next)
    {
        if (FinanceLock::lockedFor(Auth::user())) {
            return redirect()->route('finance.closed');
        }
        return $next($request);
    }
}
