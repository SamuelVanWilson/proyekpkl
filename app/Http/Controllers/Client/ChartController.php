<?php
// File: app/Http/Controllers/Client/ChartController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Controller untuk halaman grafik laporan.
 * Menampilkan data numerik dari laporan harian selama 30 hari terakhir berdasarkan konfigurasi.
 */
class ChartController extends Controller
{
    /**
     * Menampilkan halaman grafik beserta dataset.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $user = Auth::user();
        // Ambil laporan 30 hari terakhir milik user
        $reports = DailyReport::where('user_id', $user->id)
            ->where('tanggal', '>=', Carbon::now()->subDays(30))
            ->orderBy('tanggal', 'asc')
            ->get();
        $labels = $reports->pluck('tanggal')->map(function ($date) {
            return Carbon::parse($date)->format('d M');
        });
        // Ambil konfigurasi kolom rekap dan pilih yang bertipe numerik
        $config = \App\Models\TableConfiguration::where('user_id', $user->id)
            ->where('table_name', 'daily_reports')
            ->first();
        $numericFields = [];
        if ($config && !empty($config->columns['rekap'])) {
            $selectedFields = [];
            foreach ($config->columns['rekap'] as $col) {
                $type = $col['type'] ?? 'text';
                // Gunakan hanya kolom yang ditandai digunakan untuk grafik jika ada setidaknya satu yang diaktifkan
                if (!empty($col['used_for_chart'])) {
                    if (in_array($type, ['number', 'rupiah', 'dollar', 'kg', 'g'])) {
                        $selectedFields[$col['name']] = $col['label'] ?? $col['name'];
                    }
                }
            }
            // Jika ada field terpilih melalui used_for_chart, pakai itu
            if (!empty($selectedFields)) {
                $numericFields = $selectedFields;
            } else {
                // Kalau tidak, fallback ke semua numeric type
                foreach ($config->columns['rekap'] as $col) {
                    $type = $col['type'] ?? 'text';
                    if (in_array($type, ['number', 'rupiah', 'dollar', 'kg', 'g'])) {
                        $numericFields[$col['name']] = $col['label'] ?? $col['name'];
                    }
                }
            }
        }
        // Jika masih kosong, fallback ke kolom bawaan dari tabel lama
        if (empty($numericFields)) {
            if (Schema::hasColumn('daily_reports', 'total_uang')) {
                $numericFields['total_uang'] = 'Total Uang';
            }
            if (Schema::hasColumn('daily_reports', 'total_netto')) {
                $numericFields['total_netto'] = 'Total Netto';
            }
        }
        $datasets = [];
        $parseNumber = function ($value) {
            if (is_null($value) || $value === '') {
                return 0.0;
            }
            // Hapus semua karakter kecuali angka, koma, dan titik
            $cleaned = preg_replace('/[^\d,.]/', '', (string) $value);
            // Ganti koma dengan titik
            $cleaned = str_replace(',', '.', $cleaned);
            // Hapus titik ribuan kecuali yang terakhir (desimal)
            $parts = explode('.', $cleaned);
            $decimal = array_pop($parts);
            $cleaned = str_replace('.', '', implode('.', $parts)) . '.' . $decimal;
            return (float) $cleaned;
        };
        foreach ($numericFields as $fieldName => $label) {
            $datasets[$fieldName] = [
                'label' => $label,
                'data'  => [],
            ];
        }
        foreach ($reports as $report) {
            foreach ($numericFields as $fieldName => $label) {
                $value = 0;
                // Ambil dari rekap jika ada
                if (!empty($report->data['rekap'][$fieldName] ?? null)) {
                    $value = $parseNumber($report->data['rekap'][$fieldName]);
                } elseif (isset($report->$fieldName)) {
                    $value = $parseNumber($report->$fieldName);
                }
                $datasets[$fieldName]['data'][] = $value;
            }
        }
        return view('client.grafik.index', [
            'labels'   => $labels,
            'datasets' => $datasets,
        ]);
    }
}