<?php
// File: app/Http/Controllers/Client/ChartController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;
use Carbon\Carbon;

class ChartController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil data laporan 30 hari terakhir
        $reports = DailyReport::where('user_id', $user->id)
            ->where('tanggal', '>=', Carbon::now()->subDays(30))
            ->orderBy('tanggal', 'asc')
            ->get();

        // Siapkan data untuk Chart.js
        $labels = $reports->pluck('tanggal')->map(function ($date) {
            return $date->format('d M');
        });

        $dataTotalUang = $reports->pluck('total_uang');
        $dataTotalNetto = $reports->pluck('total_netto');

        // Pastikan Anda membuat view 'client.Chart.index'
        return view('client.grafik.index', compact('labels', 'dataTotalUang', 'dataTotalNetto'));
    }
}
