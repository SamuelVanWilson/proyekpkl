<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Pastikan menunjuk ke tabel 'users'
            'tanggal_lahir' => ['required', 'date'],
            'alamat' => ['required', 'string', 'max:255'],
            'pekerjaan' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'pekerjaan' => $request->pekerjaan,
            'offer_expires_at' => now()->addDays(3),
        ]);

        // Mengirim event untuk email verifikasi
        event(new Registered($user));

        // Login pengguna yang baru terdaftar
        Auth::login($user);

        // Arahkan ke halaman pemberitahuan verifikasi
        return redirect()->route('verification.notice');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Untuk client, selalu arahkan ke dashboard client.
            // Dashboard client akan menangani pengalihan berdasarkan status verifikasi dan langganan.
            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/'); // Lebih baik arahkan ke halaman utama setelah logout
    }
}
