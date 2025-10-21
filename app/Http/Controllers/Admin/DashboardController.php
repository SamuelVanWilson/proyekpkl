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
        // Ambil laporan terbaru beserta user untuk log aktivitas dengan pagination
        // Gunakan page name khusus agar dua pagination tidak berbenturan dalam query string
        $recentReports = DailyReport::with('user')
            ->orderByDesc('updated_at')
            ->paginate(5, ['*'], 'recent_page');
        // Hitung jumlah laporan per klien untuk insight dengan pagination
        $reportCounts = User::where('role', 'user')
            ->withCount('dailyReports')
            ->orderByDesc('daily_reports_count', 'desc')
            ->paginate(3, ['*'], 'top_clients_page');
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
