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

// Tambahkan trait untuk otorisasi agar metode authorize() tersedia
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;
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
     * Tampilkan halaman laporan harian standar (biasa).
     */
    public function biasa()
    {
        // Halaman ini memuat komponen Livewire laporan.create-laporan
        return view('client.laporan.biasa');
    }

    /**
     * Tampilkan halaman laporan advanced. Pastikan pengguna telah berlangganan.
     */
    public function advanced()
    {
        return view('client.laporan.advanced');
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

    public function saveFormBuilder(Request $request, User $user = null) // Tambahkan User $user = null untuk Client
    {
        // Validasi sekarang memeriksa 'label' bukan 'name' untuk input pengguna
        $validated = $request->validate([
            'rincian' => 'sometimes|array',
            'rincian.*.label' => 'required_with:rincian|string', // Cek 'label'
            'rincian.*.type' => 'required_with:rincian|string',

            'rekap' => 'sometimes|array',
            'rekap.*.label' => 'required_with:rekap|string', // Cek 'label'
            'rekap.*.type' => 'required_with:rekap|string',
            'rekap.*.formula' => 'nullable|string',
            'rekap.*.default_value' => 'nullable|string',
            'rekap.*.readonly' => 'sometimes|boolean',
        ]);

        $processedColumns = [];

        // Proses Tabel Rincian
        if (!empty($validated['rincian'])) {
            foreach ($validated['rincian'] as $col) {
                $processedColumns['rincian'][] = [
                    // 'name' dibuat secara otomatis dari 'label'
                    'name' => \Illuminate\Support\Str::slug($col['label'], '_'),
                    'label' => $col['label'], // Simpan 'label' asli
                    'type' => $col['type'],
                ];
            }
        } else {
            $processedColumns['rincian'] = [];
        }

        // Proses Formulir Rekapitulasi
        if (!empty($validated['rekap'])) {
            foreach ($validated['rekap'] as $col) {
                $processedColumns['rekap'][] = [
                    'name' => \Illuminate\Support\Str::slug($col['label'], '_'),
                    'label' => $col['label'],
                    'type' => $col['type'],
                    'formula' => $col['formula'] ?? null,
                    'default_value' => $col['default_value'] ?? null,
                    'readonly' => !empty($col['readonly']),
                ];
            }
        } else {
            $processedColumns['rekap'] = [];
        }

        // Logika untuk menentukan user_id
        $userId = $user ? $user->id : Auth::id();

        TableConfiguration::updateOrCreate(
            ['user_id' => $userId, 'table_name' => 'daily_reports'],
            ['columns' => $processedColumns]
        );

        // Redirect yang sesuai untuk setiap peran
        if ($user) {
            return redirect()->route('admin.users.index')->with('success', 'Konfigurasi form untuk ' . $user->name . ' berhasil disimpan.');
        } else {
            return redirect()->route('client.laporan.harian')->with('success', 'Struktur laporan Anda berhasil diperbarui!');
        }
    }


    // Terapkan pada method showFormBuilder() di KEDUA controller
    public function showFormBuilder(User $user = null)
    {
        $userId = $user ? $user->id : Auth::id();

        $config = TableConfiguration::firstOrNew(
            ['user_id' => $userId, 'table_name' => 'daily_reports'],
            ['columns' => ['rincian' => [], 'rekap' => []]]
        );

        $columns = $config->columns;

        // --- LOGIKA BARU: Pastikan 'label' selalu ada ---
        // Loop untuk Rincian
        if (!empty($columns['rincian'])) {
            foreach ($columns['rincian'] as $key => $col) {
                if (empty($col['label'])) {
                    // Jika tidak ada label (data lama), buat dari nama
                    // Ubah underscore menjadi spasi dan kapitalisasi setiap kata
                    $columns['rincian'][$key]['label'] = ucwords(str_replace('_', ' ', $col['name']));
                }
            }
        }

        // Loop untuk Rekapitulasi
        if (!empty($columns['rekap'])) {
            foreach ($columns['rekap'] as $key => $col) {
                if (empty($col['label'])) {
                    $columns['rekap'][$key]['label'] = ucwords(str_replace('_', ' ', $col['name']));
                }
            }
        }

        if ($user) {
            return view('admin.users.form-builder', compact('user', 'config', 'columns'));
        } else {
            return view('client.laporan.form-builder', compact('columns'));
        }
    }
}
