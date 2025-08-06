<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\TableConfiguration;
use App\Models\DailyReport;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman laporan "live" untuk hari ini.
     * Halaman ini ditenagai oleh komponen Livewire 'Laporan\Harian'.
     */
    public function harian()
    {
        // View ini hanya bertugas memuat komponen Livewire
        return view('client.laporan.harian');
    }

    /**
     * Menampilkan halaman histori (daftar laporan lama).
     */
    public function histori()
    {
        $user = Auth::user();
        $reports = DailyReport::where('user_id', $user->id)
                              ->orderBy('tanggal', 'desc')
                              ->paginate(15);

        return view('client.laporan.histori', compact('reports'));
    }

    /**
     * Menampilkan preview PDF dari halaman histori.
     */
    public function previewPdf(DailyReport $dailyReport)
    {
        // Pastikan klien hanya bisa melihat laporannya sendiri
        $this->authorize('view', $dailyReport);

        // Di sini kita perlu logika untuk merender PDF dari data JSON
        // Untuk sekarang, kita pass datanya saja
        $data = [
            'report' => $dailyReport,
            // Anda perlu mengambil konfigurasi form untuk render PDF yang dinamis
        ];

        return view('client.laporan.pdf_template', $data);
        // $pdf = Pdf::loadView('client.laporan.pdf_template', $data);
        // return $pdf->stream('laporan-'. $dailyReport->tanggal .'.pdf');
    }

    public function showFormBuilder()
    {
        // PERBAIKAN: Mengambil konfigurasi atau membuat instance baru dengan struktur default
        $config = TableConfiguration::firstOrNew(
            ['user_id' => Auth::id(), 'table_name' => 'daily_reports'],
            ['columns' => ['rincian' => [], 'rekap' => []]] // Default value jika tidak ada
        );

        // PERBAIKAN: Mengirim variabel 'columns' yang dibutuhkan oleh view
        $columns = $config->columns;

        return view('client.laporan.form-builder', compact('columns'));
    }

    public function saveFormBuilder(Request $request)
    {
        // PERBAIKAN: Menambahkan validasi untuk 'readonly'
        $validated = $request->validate([
            'rincian' => 'sometimes|array',
            'rincian.*.name' => 'required_with:rincian|string',
            'rincian.*.label' => 'nullable|string',
            'rincian.*.type' => 'required_with:rincian|string|in:text,number',

            'rekap' => 'sometimes|array',
            'rekap.*.name' => 'required_with:rekap|string',
            'rekap.*.label' => 'nullable|string',
            'rekap.*.type' => 'required_with:rekap|string|in:text,number,date',
            'rekap.*.formula' => 'nullable|string',
            'rekap.*.readonly' => 'sometimes|boolean',
        ]);

        // Membersihkan data 'readonly'
        $rekapColumns = $validated['rekap'] ?? [];
        foreach ($rekapColumns as $index => $column) {
             // Pastikan 'readonly' ada dan bernilai boolean
            $rekapColumns[$index]['readonly'] = !empty($column['readonly']);
        }

        TableConfiguration::updateOrCreate(
            ['user_id' => Auth::id(), 'table_name' => 'daily_reports'],
            [
                'columns' => [
                    'rincian' => $validated['rincian'] ?? [],
                    'rekap' => $rekapColumns,
                ]
            ]
        );

        return redirect()->route('client.laporan.harian')->with('success', 'Struktur laporan Anda berhasil diperbarui!');
    }
}
