<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'kode_unik' => 'required|string',
        ]);

        $user = User::where('kode_unik', $credentials['kode_unik'])->first();

        if ($user && $user->is_active) {
            Auth::login($user);
            $request->session()->regenerate();

            // Arahkan berdasarkan role menggunakan nama route yang sudah benar
            if ($user->role === 'admin') {
                // Nama route lengkapnya adalah 'admin.dashboard'
                return redirect()->intended(route('admin.dashboard')); 
            }
            
            // Nama route lengkapnya adalah 'client.laporan.index'
            return redirect()->intended(route('client.laporan.index'));
        }

        return back()->withErrors([
            'kode_unik' => 'Kode unik tidak valid atau akun tidak aktif.',
        ])->onlyInput('kode_unik');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
