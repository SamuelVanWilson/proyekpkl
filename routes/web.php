<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\ChartController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

// == 1. RUTE PUBLIK (GUEST) ==
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store'); // Beri nama untuk proses login
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// == 2. RUTE KLIEN / PENGGUNA (AUTH) ==
Route::middleware(['auth'])->prefix('app')->name('client.')->group(function () {
    // Redirect /app ke halaman laporan
    Route::get('/', function() {
        return redirect()->route('client.laporan.index');
    });

    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
    // ... rute klien lainnya tetap sama ...
    Route::get('/grafik', [ChartController::class, 'index'])->name('grafik.index');
    Route::get('/profil', [ProfileController::class, 'index'])->name('profil.index');
});


// == 3. RUTE ADMIN (AUTH & ADMIN) ==
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Redirect /admin ke halaman dashboard
    Route::get('/', function() {
        return redirect()->route('admin.dashboard');
    });
    
    // Nama route di sini HANYA 'dashboard', bukan 'admin.dashboard'
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Klien/User
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{user}/activity', [AdminUserController::class, 'showActivity'])->name('users.activity');
});
