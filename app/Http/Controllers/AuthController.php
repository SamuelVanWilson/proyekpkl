<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        // Ambil semua user aktif untuk dicek satu per satu
        $users = User::where('is_active', true)->get();

        foreach ($users as $user) {
            // Cek apakah kode unik yang diinput cocok dengan hash di database
            if (Hash::check($credentials['kode_unik'], $user->kode_unik)) {
                Auth::login($user);
                $request->session()->regenerate();

                if ($user->role === 'admin') {
                    return redirect()->intended(route('admin.dashboard'));
                }

                return redirect()->intended(route('client.report.index'));
            }
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
