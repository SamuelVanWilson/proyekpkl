<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah pengguna sudah login DAN memiliki role 'user'
        if (Auth::check() && Auth::user()->role === 'user') {
            // Jika ya, izinkan akses.
            return $next($request);
        }

        // Jika tidak (misalnya admin yang mencoba masuk), tendang keluar.
        Auth::logout();
        return redirect()->route('login')->withErrors(['kode_unik' => 'Akses tidak sah.']);
    }
}
