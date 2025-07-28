<?php
// File: app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyReport;

class DashboardController extends Controller
{
    public function index()
    {
        $totalClients = User::where('role', 'user')->count();
        $totalReports = DailyReport::count();
        $recentReports = DailyReport::with('user')->latest('tanggal')->take(5)->get();

        // Pastikan Anda membuat view 'admin.dashboard'
        return view('admin.dashboard', compact('totalClients', 'totalReports', 'recentReports'));
    }
}
