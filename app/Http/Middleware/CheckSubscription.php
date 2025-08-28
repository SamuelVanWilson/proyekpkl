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
            // Jika tidak, tendang ke halaman langganan dengan pesan
            return redirect()->route('subscribe.show')->with('warning', 'Anda harus berlangganan untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
