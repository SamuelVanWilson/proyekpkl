<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        // Cek jika pengguna sudah login dan TIDAK punya langganan aktif
        if (Auth::check() && !Auth::user()->hasActiveSubscription()) {
            // Jika belum berlangganan, alihkan ke halaman langganan di dalam grup client
            return redirect()->route('client.subscribe.show')->with('warning', 'Anda harus berlangganan untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
