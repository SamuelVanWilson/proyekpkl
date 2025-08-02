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
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    $user = Auth::user();
    return $user->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('client.laporan.harian'); // <-- DIARAHKAN KE HALAMAN "LIVE" BARU
});

// Rute Publik (Guest), sudah benar.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// == RUTE KLIEN / PENGGUNA (AUTH) ==
Route::middleware(['auth', 'client'])->prefix('app')->name('client.')->group(function () {
    // Rute '/app' sekarang akan mengarah ke halaman laporan harian
    Route::get('/', fn() => redirect()->route('client.laporan.harian'));

    // --- PEROMBAKAN UTAMA PADA RUTE LAPORAN ---
    // Halaman utama klien adalah 'harian'
    Route::get('/laporan', [ReportController::class, 'harian'])->name('laporan.harian');
    // Halaman sekunder untuk melihat daftar laporan lama
    Route::get('/laporan/histori', [ReportController::class, 'histori'])->name('laporan.histori');
    // Rute untuk melihat PDF dari histori
    Route::get('/laporan/histori/{dailyReport}/preview-pdf', [ReportController::class, 'previewPdf'])->name('laporan.histori.pdf');

    // --- Rute Halaman Lain (tetap sama) ---
    Route::get('/grafik', [ChartController::class, 'index'])->name('grafik.index');
    Route::get('/profil', [ProfileController::class, 'index'])->name('profil.index');
});

// == RUTE ADMIN (AUTH & ADMIN) ==
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::get('/users/{user}/activity', [AdminUserController::class, 'showActivity'])->name('users.activity');
    Route::get('/users/{user}/form-builder', [AdminUserController::class, 'showFormBuilder'])->name('users.form-builder');
    Route::post('/users/{user}/form-builder', [AdminUserController::class, 'saveFormBuilder'])->name('users.form-builder.store');
});
