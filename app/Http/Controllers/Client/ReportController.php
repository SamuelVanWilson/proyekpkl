<?php
// File: app/Http/Controllers/Client/LaporanController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DailyReport;
use App\Models\Barang;
use App\Models\PdfExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman daftar laporan utama.
     */
    public function index()
    {
        $user = Auth::user();
        // Menggunakan paginate untuk membatasi jumlah data per halaman
        $reports = DailyReport::where('user_id', $user->id)
                              ->orderBy('tanggal', 'desc')
                              ->paginate(15);

        // Pastikan Anda membuat view 'client.laporan.index'
        return view('client.laporan.index', compact('reports'));
    }

    /**
     * Menampilkan form untuk membuat laporan baru.
     */
    public function create()
    {
        // Pastikan Anda membuat view 'client.laporan.create'
        return view('client.laporan.create');
    }

    /**
     * Menyimpan laporan baru (rekapitulasi & rincian).
     */
    public function store(Request $request)
    {
        // Contoh validasi, sesuaikan dengan kebutuhan di frontend
        $validated = $request->validate([
            'rekap.tanggal' => 'required|date',
            'rekap.lokasi' => 'required|string|max:255',
            'rekap.pemilik_sawah' => 'required|string|max:255',
            'rekap.karung_kosong' => 'required|numeric|min:0',
            'rekap.harga_per_kilo' => 'required|numeric|min:0',
            'rekap.uang_muka' => 'required|numeric|min:0',
            'rincian' => 'required|array|min:1',
            'rincian.*.total' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = Auth::user();
            $rekapData = $validated['rekap'];
            $rincianData = $validated['rincian'];

            // Lakukan kalkulasi otomatis
            $totalBruto = collect($rincianData)->sum('total');
            $totalNetto = $totalBruto - $rekapData['karung_kosong'];
            $hargaBruto = $totalNetto * $rekapData['harga_per_kilo'];
            $totalUang = $hargaBruto - $rekapData['uang_muka'];

            // 1. Buat data Rekapitulasi (DailyReport)
            $dailyReport = DailyReport::create([
                'user_id' => $user->id,
                'tanggal' => $rekapData['tanggal'],
                'lokasi' => $rekapData['lokasi'],
                'pemilik_sawah' => $rekapData['pemilik_sawah'],
                'jumlah_karung' => count($rincianData),
                'total_bruto' => $totalBruto,
                'karung_kosong' => $rekapData['karung_kosong'],
                'total_netto' => $totalNetto,
                'harga_per_kilo' => $rekapData['harga_per_kilo'],
                'harga_bruto' => $hargaBruto,
                'uang_muka' => $rekapData['uang_muka'],
                'total_uang' => $totalUang,
            ]);

            // 2. Buat data Rincian (Barang)
            foreach ($rincianData as $item) {
                Barang::create([
                    'user_id' => $user->id,
                    'daily_report_id' => $dailyReport->id,
                    'data' => $item, // Simpan semua data rincian sebagai JSON
                ]);
            }

            return redirect()->route('client.laporan.index')->with('success', 'Laporan berhasil disimpan.');
        });
    }

    /**
     * Menampilkan form untuk mengedit laporan.
     */
    public function edit(DailyReport $report)
    {
        $this->authorize('update', $report); // Gunakan Policy
        $report->load('rincianBarang'); // Load data rincian untuk di-pass ke view
        return view('client.laporan.edit', compact('report'));
    }

    /**
     * Memperbarui laporan yang sudah ada.
     */
    public function update(Request $request, DailyReport $report)
    {
        $this->authorize('update', $report); // Gunakan Policy

        // Logika validasi dan update mirip dengan store()
        // ... (Tambahkan logika validasi di sini) ...

        return DB::transaction(function () use ($request, $report) {
            // Hapus data rincian lama
            $report->rincianBarang()->delete();
            
            // Logika untuk kalkulasi dan update sama seperti di method store()
            // ... (Tambahkan logika kalkulasi dan update di sini) ...
            
            // Simpan rincian baru
            // ... (Tambahkan loop untuk menyimpan rincian baru) ...

            return redirect()->route('client.laporan.index')->with('success', 'Laporan berhasil diperbarui.');
        });
    }

    /**
     * Menghapus sebuah laporan.
     */
    public function destroy(DailyReport $report)
    {
        $this->authorize('delete', $report); // Gunakan Policy

        // Menggunakan onDelete('cascade') di migrasi,
        // data rincian di tabel 'barangs' akan otomatis terhapus.
        $report->delete();

        return redirect()->route('client.laporan.index')->with('success', 'Laporan berhasil dihapus.');
    }
    
    // Logika untuk Preview dan Export PDF tetap sama
    public function previewPdf(DailyReport $report)
    {
        $this->authorize('view', $report);
        $data = ['report' => $report->load('rincianBarang')];
        $pdf = Pdf::loadView('client.laporan.pdf_template', $data);
        return $pdf->stream('preview-laporan.pdf');
    }

    public function exportPdf(DailyReport $report)
    {
        $this->authorize('view', $report);
        $data = ['report' => $report->load('rincianBarang')];
        $pdf = Pdf::loadView('client.laporan.pdf_template', $data);
        $filename = 'laporan-' . $report->tanggal->format('Y-m-d') . '-' . Str::slug($report->lokasi) . '.pdf';

        PdfExport::create([
            'user_id' => Auth::id(),
            'daily_report_id' => $report->id,
            'filename' => $filename,
            'data_snapshot' => $data,
        ]);
        
        return $pdf->download($filename);
    }
}
