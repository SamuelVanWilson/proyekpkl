<?php
// File: app/Http/Controllers/Client/ChartController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ChartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Ambil laporan 30 hari terakhir
        $reports = DailyReport::where('user_id', $user->id)
            ->where('tanggal', '>=', Carbon::now()->subDays(30))
            ->orderBy('tanggal', 'asc')
            ->get();

        // Label tanggal untuk grafik
        $labels = $reports->pluck('tanggal')->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('d M');
        });

        // Ambil konfigurasi kolom rekap untuk menentukan kolom numerik
        $config = \App\Models\TableConfiguration::where('user_id', $user->id)
            ->where('table_name', 'daily_reports')
            ->first();
        $numericFields = [];
        if ($config && !empty($config->columns['rekap'])) {
            foreach ($config->columns['rekap'] as $col) {
                // Jenis data yang dianggap numerik
                $type = $col['type'] ?? 'text';
                if (in_array($type, ['number', 'rupiah', 'dollar', 'kg', 'g'])) {
                    $numericFields[$col['name']] = $col['label'] ?? $col['name'];
                }
            }
        }
        // Jika tidak ada numericFields, gunakan fallback bawaan total_uang dan total_netto bila tersedia pada model
        if (empty($numericFields)) {
            // Cek apakah model memiliki kolom total_uang dan total_netto
            if (Schema::hasColumn('daily_reports', 'total_uang')) {
                $numericFields['total_uang'] = 'Total Uang';
            }
            if (Schema::hasColumn('daily_reports', 'total_netto')) {
                $numericFields['total_netto'] = 'Total Netto';
            }
        }
        // Inisialisasi dataset
        $datasets = [];
        // Fungsi untuk parsing angka dari string
        $parseNumber = function ($value) {
            if (is_null($value) || $value === '') {
                return 0.0;
            }
            // Hapus semua karakter kecuali angka, koma, dan titik
            $cleaned = preg_replace('/[^\d,.]/', '', (string) $value);
            // Ganti koma menjadi titik untuk desimal
            $cleaned = str_replace(',', '.', $cleaned);
            // Hapus titik ribuan
            // Caranya: buang semua titik kecuali yang terakhir
            $parts = explode('.', $cleaned);
            $decimal = array_pop($parts);
            $cleaned = str_replace('.', '', implode('.', $parts)) . '.' . $decimal;
            return (float) $cleaned;
        };

        foreach ($numericFields as $fieldName => $label) {
            $datasets[$fieldName] = [
                'label' => $label,
                'data' => [],
            ];
        }
        // Mengisi data
        foreach ($reports as $report) {
            foreach ($numericFields as $fieldName => $label) {
                $value = 0;
                // Cek pada data rekap terlebih dahulu
                if (!empty($report->data['rekap'][$fieldName] ?? null)) {
                    $value = $parseNumber($report->data['rekap'][$fieldName]);
                } elseif (isset($report->$fieldName)) {
                    // Gunakan nilai kolom di database jika ada
                    $value = $parseNumber($report->$fieldName);
                }
                $datasets[$fieldName]['data'][] = $value;
            }
        }

        return view('client.grafik.index', [
            'labels' => $labels,
            'datasets' => $datasets,
        ]);
    }
}
