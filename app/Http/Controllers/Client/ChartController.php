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
            $rekapCols = $config->columns['rekap'];
            // Periksa apakah konfigurasi mendefinisikan kunci used_for_chart pada salah satu kolom.
            $hasChartSetting = false;
            foreach ($rekapCols as $col) {
                if (array_key_exists('used_for_chart', $col)) {
                    $hasChartSetting = true;
                    break;
                }
            }
            foreach ($rekapCols as $col) {
                $type = $col['type'] ?? 'text';
                // Tentukan apakah kolom bertipe numerik sesuai jenis yang kita dukung
                $isNumericType = in_array($type, ['number', 'rupiah', 'dollar', 'kg', 'g']);
                if (!$isNumericType) {
                    continue;
                }
                if ($hasChartSetting) {
                    // Jika konfigurasi memiliki properti used_for_chart, hanya kolom yang ditandai benar-benar true
                    // yang disertakan. Nilai '1', true, atau truthy lainnya akan dianggap aktif.
                    if (!empty($col['used_for_chart'])) {
                        $numericFields[$col['name']] = $col['label'] ?? $col['name'];
                    }
                } else {
                    // Konfigurasi lama tanpa properti used_for_chart: sertakan semua kolom numerik.
                    $numericFields[$col['name']] = $col['label'] ?? $col['name'];
                }
            }
        }
        // Jika masih kosong setelah membaca konfigurasi, coba gunakan kolom bawaan dari skema lama
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