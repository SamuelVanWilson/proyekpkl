<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\ChartController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Auth;

// Gerbang utama yang akan mengarahkan user yang sudah login
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    // PERBAIKAN UTAMA DI SINI:
    // Menggunakan nama rute 'client.laporan.index' yang benar
    return redirect()->route('client.laporan.index');
});

// == RUTE PUBLIK (GUEST) ==
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// == RUTE KLIEN / PENGGUNA (AUTH) ==
Route::middleware(['auth', 'client'])->prefix('app')->name('client.')->group(function () {
    Route::get('/', fn() => redirect()->route('client.laporan.index'));

    Route::resource('laporan', ReportController::class)->except(['show']);

    Route::get('/laporan/{dailyReport}/preview-pdf', [ReportController::class, 'previewPdf'])->name('laporan.pdf.preview');
    Route::get('/laporan/{dailyReport}/export-pdf', [ReportController::class, 'exportPdf'])->name('laporan.pdf.export');
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
