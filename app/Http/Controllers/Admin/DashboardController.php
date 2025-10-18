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
        // Ambil laporan terbaru beserta user untuk log aktivitas
        $recentReports = DailyReport::with('user')->orderByDesc('updated_at')->take(5)->get();
        // Hitung jumlah laporan per klien (top 5) untuk insight
        $reportCounts = User::where('role', 'user')
            ->withCount('dailyReports')
            ->orderBy('daily_reports_count', 'desc')
            ->take(5)
            ->get();
        // Hitung total laporan berdasarkan jenis (Advanced/Biasa/Lama)
        $advancedCount = 0;
        $biasaCount    = 0;
        foreach (DailyReport::all() as $rep) {
            $data = $rep->data ?? [];
            if (!empty($data['rincian']) || !empty($data['rekap'])) {
                $advancedCount++;
            } elseif (!empty($data['rows'])) {
                $biasaCount++;
            } else {
                $lamaCount++;
            }
        }
        $reportTypeCounts = [
            'advanced' => $advancedCount,
            'biasa'    => $biasaCount,
        ];
        return view('admin.dashboard', [
            'totalClients'     => $totalClients,
            'totalReports'     => $totalReports,
            'recentReports'    => $recentReports,
            'reportCounts'     => $reportCounts,
            'reportTypeCounts' => $reportTypeCounts,
        ]);
    }
}
