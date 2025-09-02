<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\ChartController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Auth;

// Gerbang utama, sudah benar.
// Route::get('/', function () {
//     $user = Auth::user();
//     return $user->role === 'admin'
//         ? redirect()->route('admin.dashboard')
//         : redirect()->route('client.laporan.harian'); // <-- DIARAHKAN KE HALAMAN "LIVE" BARU
// });

// Rute untuk pengguna yang belum login (tamu)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterStep1'])->name('register');

    Route::post('/register/step1', [AuthController::class, 'postRegisterStep1'])->name('register.step1.post');

    Route::get('/register/step2', [AuthController::class, 'showRegisterStep2'])->name('register.step2.show');
    Route::post('/register/step2', [AuthController::class, 'postRegisterStep2'])->name('register.step2.post');

    Route::get('/register/step3', [AuthController::class, 'showRegisterStep3'])->name('register.step3.show');
    Route::post('/register/step3', [AuthController::class, 'postRegisterStep3'])->name('register.step3.post');

    // Rute sederhana untuk halaman lupa password. Ini bukan implementasi reset password Laravel
    // secara penuh, namun setidaknya menghindari error rute tidak ditemukan. Anda bisa
    // menyesuaikan implementasi sesuai kebutuhan (misal, menambahkan fitur kirim email).
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::get('/register/consent', [AuthController::class, 'showRegisterConsent'])->name('register.consent.show');
    Route::post('/register/consent', [AuthController::class, 'postRegisterConsent'])->name('register.consent.post');
});

/*
|--------------------------------------------------------------------------
| Rute Terkait Email Verifikasi
|--------------------------------------------------------------------------
*/

// Halaman yang meminta pengguna untuk verifikasi
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Rute yang diakses dari link di email
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('client.dashboard'); // Arahkan ke dashboard setelah verifikasi
})->middleware(['auth', 'signed'])->name('verification.verify');

// Rute untuk mengirim ulang link verifikasi
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Link verifikasi baru telah dikirim ke email Anda!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');



/*
|--------------------------------------------------------------------------
| Rute Aplikasi Utama (Setelah Login)   
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // --- GRUP UNTUK ADMIN ---
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn() => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', AdminUserController::class)->except(['show', 'create', 'store']);
        Route::get('/users/{user}/activity', [AdminUserController::class, 'showActivity'])->name('users.activity');
        Route::get('/users/{user}/form-builder', [AdminUserController::class, 'showFormBuilder'])->name('users.form-builder');
        Route::post('/users/{user}/form-builder', [AdminUserController::class, 'saveFormBuilder'])->name('users.form-builder.store');
    });

    // --- GRUP UNTUK CLIENT ---
    Route::middleware('client')->prefix('app')->name('client.')->group(function () {

        // Pengguna akan diarahkan ke sini setelah login
        Route::get('/', function() {
            $user = auth()->user();
            // Jika email belum diverifikasi, paksa ke halaman verifikasi
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }
            // Arahkan sesuai status langganan
            // Perbaiki nama rute: bagi pengguna non‑premium arahkan ke laporan harian (laporan biasa)
            $targetRoute = $user->hasActiveSubscription() ? 'client.laporan.advanced' : 'client.laporan.harian';
            return redirect()->route($targetRoute);
        })->name('dashboard');

        // --- FITUR GRATIS (Hanya butuh verifikasi email) ---
        Route::middleware('verified')->group(function() {
            Route::get('/laporan-biasa', [ReportController::class, 'biasa'])->name('laporan.harian');
            Route::get('/laporan/histori', [ReportController::class, 'histori'])->name('laporan.histori');
            Route::get('/laporan/histori/{dailyReport}/preview-pdf', [ReportController::class, 'previewPdf'])->name('laporan.histori.pdf');
            // Unduh PDF laporan
            Route::get('/laporan/histori/{dailyReport}/download', [ReportController::class, 'downloadPdf'])->name('laporan.histori.download');

            // Halaman preview PDF dengan opsi judul & logo serta export, dapat diakses setelah laporan disimpan
            Route::get('/laporan/{dailyReport}/preview', [ReportController::class, 'preview'])->name('laporan.preview');
            Route::post('/laporan/{dailyReport}/preview', [ReportController::class, 'updatePreview'])->name('laporan.preview.update');
            // Profil
            Route::get('/profil', [ProfileController::class, 'index'])->name('profil.index');
            // Halaman edit profil terpisah
            Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profil.edit');
            // Perbarui profil
            Route::post('/profil/update', [ProfileController::class, 'update'])->name('profil.update');
            // Nonaktifkan akun
            Route::post('/profil/deactivate', [ProfileController::class, 'deactivate'])->name('profil.deactivate');
            Route::get('/berlangganan', [ProfileController::class, 'show'])->name('subscribe.show');
            Route::post('/berlangganan/proses', [ProfileController::class, 'process'])->name('subscribe.process');
            // Pilih paket langganan, memulai proses pembuatan pesanan dan snap token
            Route::post('/berlangganan/{plan}', [ProfileController::class, 'start'])->name('subscribe.plan');
        });

        // --- FITUR PREMIUM (Butuh verifikasi email DAN langganan aktif) ---
        Route::middleware(['verified', 'subscribed'])->group(function () {
            Route::get('/laporan-advanced', [ReportController::class, 'advanced'])->name('laporan.advanced');
            Route::get('/grafik', [ChartController::class, 'index'])->name('grafik.index');
            Route::get('/laporan/form-builder', [ReportController::class, 'showFormBuilder'])->name('laporan.form-builder');
            Route::post('/laporan/form-builder', [ReportController::class, 'saveFormBuilder'])->name('laporan.form-builder.save');
            // Tambahkan rute premium lainnya di sini
        });

        // Rute tambahan untuk edit dan hapus laporan, tersedia untuk laporan apapun yang dimiliki pengguna
        Route::middleware('verified')->group(function () {
            // Edit laporan: jika laporan biasa, gunakan halaman laporan biasa dengan parameter report
            Route::get('/laporan/{dailyReport}/edit', [ReportController::class, 'edit'])->name('laporan.edit');
            // Hapus laporan
            Route::delete('/laporan/{dailyReport}', [ReportController::class, 'destroy'])->name('laporan.destroy');
        });
    });
});
