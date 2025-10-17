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
            // Periksa apakah setidaknya satu kolom numerik memiliki nilai used_for_chart yang aktif (truthy).
            // Hanya kolom dengan tipe numerik yang dapat memicu mode "kustom" grafik. Jika sebuah
            // kolom bertipe non-numerik ditandai untuk grafik, itu diabaikan untuk keperluan
            // menentukan apakah ada konfigurasi khusus. Dengan demikian, ketika semua kolom
            // numerik tidak ditandai, grafik akan menampilkan kolom bawaan (total_uang/total_netto).
            $hasChartSetting = false;
            foreach ($rekapCols as $col) {
                $typeCheck = $col['type'] ?? 'text';
                $isNumericCheck = in_array($typeCheck, ['number', 'rupiah', 'dollar', 'kg', 'g']);
                if ($isNumericCheck && !empty($col['used_for_chart'])) {
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
                    // Jika ada setidaknya satu kolom bertanda used_for_chart, maka hanya kolom yang ditandai
                    // benar‑benar aktif (truthy) yang disertakan. Nilai '1', true, atau truthy lainnya akan dianggap aktif.
                    if (!empty($col['used_for_chart'])) {
                        $numericFields[$col['name']] = $col['label'] ?? $col['name'];
                    }
                } else {
                    // Konfigurasi lama tanpa properti used_for_chart atau semua bernilai false: sertakan semua kolom numerik.
                    // Kolom tidak ditandai used_for_chart tidak dimasukkan ke grafik.
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
            // Fungsi ini mengekstrak nilai numerik dari berbagai format string.
            // Ia menangani angka dengan pemisah ribuan (titik atau koma), mata uang dan satuan.
            // Jika terdapat tanda desimal, karakter pemisah terakhir (titik atau koma) dianggap
            // sebagai pemisah desimal. Jika tidak ada pemisah desimal, seluruh string yang
            // terdiri dari digit akan diinterpretasikan sebagai bilangan bulat.
            if (is_null($value) || $value === '') {
                return 0.0;
            }
            // Ambil hanya karakter digit, koma, dan titik agar mata uang/satuan terhapus.
            $cleaned = preg_replace('/[^\d,.]/', '', (string) $value);
            if ($cleaned === '') {
                return 0.0;
            }
            // Cari posisi terakhir dari koma dan titik
            $lastComma = strrpos($cleaned, ',');
            $lastDot   = strrpos($cleaned, '.');
            $decimalPos = null;
            // Tentukan pemisah desimal: ambil karakter pemisah (koma atau titik) yang muncul terakhir.
            if ($lastComma !== false && $lastDot !== false) {
                $decimalPos = ($lastComma > $lastDot) ? $lastComma : $lastDot;
            } elseif ($lastComma !== false) {
                // Jika hanya ada koma, anggap sebagai desimal bila hanya satu koma.
                if (substr_count($cleaned, ',') === 1) {
                    $decimalPos = $lastComma;
                }
            } elseif ($lastDot !== false) {
                // Jika hanya ada titik, anggap sebagai desimal bila hanya satu titik.
                if (substr_count($cleaned, '.') === 1) {
                    $decimalPos = $lastDot;
                }
            }
            if ($decimalPos === null) {
                // Tidak ada pemisah desimal yang valid: hapus semua koma/titik dan kembalikan sebagai integer.
                $number = str_replace([',', '.'], '', $cleaned);
                return $number === '' ? 0.0 : (float) $number;
            }
            // Pisahkan bagian integer dan desimal berdasarkan posisi pemisah desimal.
            $integerPart = substr($cleaned, 0, $decimalPos);
            $decimalPart = substr($cleaned, $decimalPos + 1);
            // Buang semua pemisah ribuan pada kedua bagian.
            $integerPart = str_replace([',', '.'], '', $integerPart);
            $decimalPart = str_replace([',', '.'], '', $decimalPart);
            // Satukan kembali dengan titik sebagai pemisah desimal.
            $numberStr = $integerPart . '.' . $decimalPart;
            // Jika integerPart kosong (misalnya ",75"), beri nilai 0 sebelum desimal.
            if ($integerPart === '') {
                $numberStr = '0.' . $decimalPart;
            }
            return (float) $numberStr;
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