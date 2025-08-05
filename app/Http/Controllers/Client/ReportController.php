<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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

    // METHOD BARU untuk menampilkan Form Builder ke klien
    public function showFormBuilder()
    {
        $config = TableConfiguration::firstOrNew([
            'user_id' => Auth::id(),
            'table_name' => 'daily_reports'
        ]);

        return view('client.laporan.form-builder', compact('config'));
    }

    // METHOD BARU untuk menyimpan konfigurasi dari klien
    public function saveFormBuilder(Request $request)
    {
        $validated = $request->validate([
            'columns.rincian' => 'sometimes|array',
            'columns.rincian.*.name' => 'required|string',
            'columns.rincian.*.label' => 'nullable|string',
            'columns.rincian.*.type' => 'required|string|in:text,number',

            'columns.rekap' => 'sometimes|array',
            'columns.rekap.*.name' => 'required|string',
            'columns.rekap.*.label' => 'nullable|string',
            'columns.rekap.*.type' => 'required|string|in:text,number,date',
            'columns.rekap.*.formula' => 'nullable|string',
            'columns.rekap.*.readonly' => 'nullable|boolean',
        ]);

        $config = TableConfiguration::firstOrNew([
            'user_id' => Auth::id(),
            'table_name' => 'daily_reports'
        ]);

        $config->columns = [
            'rincian' => $validated['columns']['rincian'] ?? [],
            'rekap' => $validated['columns']['rekap'] ?? [],
        ];

        $config->save();

        return redirect()->route('client.laporan.harian')->with('success', 'Struktur laporan Anda berhasil diperbarui!');
    }
}
