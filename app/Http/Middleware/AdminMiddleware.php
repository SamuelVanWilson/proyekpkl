<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah pengguna sudah login DAN memiliki role 'admin'
        if (Auth::check() && Auth::user()->role === 'admin') {
            // Jika ya, izinkan akses ke halaman selanjutnya
            return $next($request);
        }

        // Jika bukan admin, alihkan ke dashboard klien agar pengguna berada pada halaman yang tepat
        return redirect()->route('client.dashboard')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
    }
}
