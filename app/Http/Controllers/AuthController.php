<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\URL;


class AuthController extends Controller
{
    protected function provinces(): array
    {
        return [
            'Aceh',
            'Bali',
            'Banten',
            'Bengkulu',
            'DI Yogyakarta',
            'DKI Jakarta',
            'Gorontalo',
            'Jambi',
            'Jawa Barat',
            'Jawa Tengah',
            'Jawa Timur',
            'Kalimantan Barat',
            'Kalimantan Selatan',
            'Kalimantan Tengah',
            'Kalimantan Timur',
            'Kalimantan Utara',
            'Kepulauan Bangka Belitung',
            'Kepulauan Riau',
            'Lampung',
            'Maluku',
            'Maluku Utara',
            'Nusa Tenggara Barat',
            'Nusa Tenggara Timur',
            'Papua',
            'Papua Barat',
            'Papua Barat Daya',
            'Papua Pegunungan',
            'Papua Selatan',
            'Papua Tengah',
            'Riau',
            'Sulawesi Barat',
            'Sulawesi Selatan',
            'Sulawesi Tengah',
            'Sulawesi Tenggara',
            'Sulawesi Utara',
            'Sumatera Barat',
            'Sumatera Selatan',
            'Sumatera Utara',
        ];
    }
    protected function jobs(): array
    {
        return [
            'Pelajar/Mahasiswa',
            'Pegawai Negeri/ASN',
            'Karyawan Swasta',
            'Wirausaha',
            'Freelancer',
            'Profesional (Dokter, Pengacara, dll)',
            'Tenaga Kesehatan',
            'Guru/Dosen',
            'Buruh/Karyawan Lepas',
            'Lainnya',
        ];
    }
    public function showRegisterStep1()
    {
        return view('auth.register.step1');
    }

    public function postRegisterStep1(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            // Disimpan sebagai string; validasi pakai date lalu format string
            // Pastikan tanggal lahir sebelum hari ini (tidak boleh hari ini atau besok)
            'tanggal_lahir' => 'required|date|before:today',
        ], [
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        $payload = [
            'name' => $data['name'],
            'tanggal_lahir' => Carbon::parse($data['tanggal_lahir'])->format('Y-m-d'),
        ];

        Session::put('register', array_merge(Session::get('register', []), $payload));
        return redirect()->route('register.step2.show');
    }

    public function showRegisterStep2()
    {
        if (!Session::has('register.name')) {
            return redirect()->route('register');
        }
        $provinces = $this->provinces();
        $jobs = $this->jobs();
        return view('auth.register.step2', compact('provinces','jobs'));
    }

    public function postRegisterStep2(Request $request)
    {
        // 1) Normalisasi input ke +62……
        $raw = trim((string) $request->input('nomor_telepon', ''));
        $raw = preg_replace('/[\s\-.]/', '', $raw); // hapus spasi, dash, titik

        if (strpos($raw, '+62') === 0) {
            $phone = $raw;
        } elseif (strpos($raw, '0') === 0) {
            $phone = '+62' . substr($raw, 1);
        } elseif (strpos($raw, '62') === 0) {
            $phone = '+' . $raw;
        } else {
            $phone = $raw; // kalau format aneh, nanti gagal di regex
        }
        $request->merge(['nomor_telepon' => $phone]);

        // 2) Validasi
        $data = $request->validate([
            'alamat'        => ['required', 'string', 'max:255', Rule::in($this->provinces())],
            'pekerjaan'     => ['nullable','string','max:255', Rule::in($this->jobs())],
            // Harus +628xxxxxxxxxx (8–11 digit setelah '8', total tetap <15 digit E.164)
            'nomor_telepon' => ['required','string','regex:/^\+628\d{8,11}$/','unique:users,nomor_telepon'],
        ], [
            'nomor_telepon.regex' => 'Nomor telepon harus format Indonesia: +62 8xxxxxxxxxx (boleh ketik 08..., akan diubah otomatis).',
            'nomor_telepon.unique' => 'Nomor telepon sudah terdaftar.',
        ]);

        Session::put('register', array_merge(Session::get('register', []), $data));
        return redirect()->route('register.step3.show');
    }


    public function showRegisterStep3()
    {
        if (!Session::has('register.alamat')) {
            return redirect()->route('register.step2.show');
        }
        return view('auth.register.step3');
    }

    public function postRegisterStep3(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email','max:255', Rule::unique('users','email')],
            'password' => 'required|string|min:8|confirmed',
        ]);

        Session::put('register', array_merge(Session::get('register', []), [
            'email' => $data['email'],
            // simpan sementara plain di session untuk di-hash nanti
            'password' => $data['password'],
        ]));

        return redirect()->route('register.consent.show');
    }

    public function showRegisterConsent()
    {
        if (!Session::has('register.email')) {
            return redirect()->route('register.step3.show');
        }
        return view('auth.register.consent');
    }

    public function postRegisterConsent(Request $request)
    {
        $request->validate([
            'agree_terms' => 'accepted',
            'agree_privacy' => 'accepted',
        ], [
            'agree_terms.accepted' => 'Anda harus menyetujui ketentuan penggunaan.',
            'agree_privacy.accepted' => 'Anda harus memahami & menyetujui kebijakan penggunaan data.',
        ]);

        $reg = Session::get('register', []);

        // Safety: pastikan semua field ada
        foreach (['name','tanggal_lahir','alamat','nomor_telepon','email','password'] as $k) {
            if (!array_key_exists($k, $reg)) {
                return redirect()->route('register')->with('error', 'Langkah registrasi tidak lengkap.');
            }
        }

        $user = User::create([
            'name' => $reg['name'],
            'email' => $reg['email'],
            'password' => Hash::make($reg['password']),

            'alamat' => $reg['alamat'] ?? null,
            'tanggal_lahir' => $reg['tanggal_lahir'] ?? null, // string
            'pekerjaan' => $reg['pekerjaan'] ?? null,
            'nomor_telepon' => $reg['nomor_telepon'],

            'role' => 'user',
            'is_active' => 1,

            'subscription_plan' => null,
            'subscription_expires_at' => null,
            'offer_expires_at' => null,
        ]);

        // Bersihkan session wizard
        Session::forget('register');

        // Kirim email verifikasi
        event(new Registered($user));

        // Auto login agar pengguna dapat melihat halaman verifikasi
        Auth::login($user);

        return redirect()->route('verification.notice')->with('success', 'Registrasi berhasil. Silakan verifikasi email Anda.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
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

    /**
     * Handle a forgot password request by sending a reset link to the user's email.
     *
     * This method uses Laravel's built‑in password broker to generate a token and
     * send a password reset email. If the email does not exist in the database,
     * the response will still indicate success to avoid disclosing user existence.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Attempt to send the reset link using the default password broker
        $status = Password::sendResetLink(
            ['email' => $request->input('email')]
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Display the password reset form for the given token.
     *
     * @param string $token
     * @return \Illuminate\Contracts\View\View
     */
    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Handle the password reset request.
     *
     * Validates the incoming request, attempts to reset the user's password using the
     * built‑in password broker, and logs the user in upon success. The token is
     * automatically invalidated after use. If the token is invalid or expired,
     * an error will be shown.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ]);
                $user->save();

                // Immediately log the user in after password reset
                Auth::login($user);
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('client.dashboard')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * This method uses Laravel Socialite to initiate the OAuth flow with Google.
     * The user will be redirected to Google's consent screen. Upon approval,
     * Google will redirect back to the application via the callback route.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Obtain the user information from Google and log the user in.
     *
     * If the user does not exist, a new account will be created with the
     * information retrieved from Google. The email will be considered verified
     * and the user will be marked as active. After creating or retrieving the
     * user, the user is logged in and redirected to their dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat mengautentikasi dengan Google.');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // Buat akun baru berdasarkan data dari Google
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Pengguna',
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(16)),
                'role' => 'user',
                'is_active' => true,
                // Tandai email sebagai terverifikasi
                'email_verified_at' => now(),
            ]);
        }

        // Login pengguna
        Auth::login($user, true);

        return redirect()->route('client.dashboard');
    }
}
