<?php

namespace App\Http\Middleware;

use App\Services\ActivityTracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Mencatat halaman yang dibuka pengguna yang sudah login (setelah halaman selesai diproses). */
class TrackUserActivity
{
    public function __construct(private ActivityTracker $tracker)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethod('GET') && Auth::check() && $response->getStatusCode() < 400) {
            $this->tracker->trackRequest($request, Auth::user());
        }

        return $response;
    }
}
