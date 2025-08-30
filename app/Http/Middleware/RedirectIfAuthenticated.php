<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect berdasarkan role
                if ($user->role === 'admin') {
                    // Jika admin sudah login, arahkan ke dashboard admin
                    return redirect()->route('admin.dashboard');
                } elseif ($user->role === 'user') {
                    // Jika pengguna sudah login, arahkan ke halaman dashboard klien.
                    // Dashboard klien akan mengarahkan ke halaman yang tepat berdasarkan status langganan.
                    return redirect()->route('client.dashboard');
                }
            }
        }

        return $next($request);
    }
}
