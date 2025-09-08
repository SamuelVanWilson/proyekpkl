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
use Illuminate\Support\Facades\Storage;
use App\Models\PdfExport;

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

    /**
     * Tampilkan form edit untuk laporan. Laporan biasa akan memuat komponen SimpleTable dengan reportId.
     */
    public function edit(DailyReport $dailyReport)
    {
        // Pastikan user memiliki laporan ini
        $this->authorize('update', $dailyReport);
        // Jika laporan biasa (memiliki data), gunakan halaman laporan biasa
        if (!empty($dailyReport->data)) {
            return view('client.laporan.biasa', ['reportId' => $dailyReport->id]);
        }
        // Untuk laporan advanced, sementara belum ada dukungan edit
        return redirect()->route('client.laporan.histori')->with('message', 'Edit laporan advanced belum tersedia.');
    }

    /**
     * Hapus laporan dari database.
     */
    public function destroy(DailyReport $dailyReport)
    {
        $this->authorize('delete', $dailyReport);
        $dailyReport->delete();
        return redirect()->route('client.laporan.histori')->with('success', 'Laporan berhasil dihapus.');
    }

    /**
     * Generate dan download PDF dari laporan menggunakan DomPDF.
     */
    public function downloadPdf(DailyReport $dailyReport)
    {
        $this->authorize('view', $dailyReport);
        $user = Auth::user();

        // Validasi: pastikan judul laporan terisi sebelum mengunduh PDF
        // Jika title kosong, jangan lanjutkan export dan berikan pesan kesalahan
        $reportTitle = $dailyReport->data['meta']['title'] ?? null;
        if (empty($reportTitle)) {
            return back()->with('error', 'Judul laporan harus diisi sebelum mengunduh PDF. Silakan isi judul pada halaman laporan atau di preview.');
        }

        // Batasi jumlah export PDF untuk pengguna non‑premium
        if (!$user->hasActiveSubscription()) {
            // Hitung ekspor PDF untuk tanggal saat ini saja agar free limit reset setiap hari
            $exportCountToday = PdfExport::where('user_id', $user->id)
                ->whereDate('exported_at', today())
                ->count();
            if ($exportCountToday >= 3) {
                return back()->with('error', 'Limit export PDF gratis (3x per hari) telah tercapai. Silakan berlangganan untuk export tanpa batas.');
            }
        }

        $pdf = Pdf::loadView('client.laporan.pdf_template', ['report' => $dailyReport]);
        $fileName = 'laporan-' . $dailyReport->tanggal . '-' . now()->timestamp . '.pdf';

        // Rekam aktivitas export PDF
        // Beberapa instalasi mungkin belum memiliki kolom 'daily_report_id' pada tabel pdf_exports.
        // Kita cek skema terlebih dahulu untuk menghindari error SQL.
        $exportData = [
            'user_id'      => $user->id,
            'filename'     => $fileName,
            'type'         => 'daily_report',
            'filters'      => null,
            'data_snapshot'=> $dailyReport->data,
            'total_items'  => 0,
            'total_pages'  => 0,
            'file_path'    => null,
            'exported_at'  => now(),
        ];
        // Tambahkan daily_report_id jika kolom tersedia di database
        if (\Illuminate\Support\Facades\Schema::hasColumn('pdf_exports', 'daily_report_id')) {
            $exportData['daily_report_id'] = $dailyReport->id;
        }
        PdfExport::create($exportData);

        return $pdf->download($fileName);
    }

    /**
     * Halaman preview PDF dengan opsi meta (judul, logo).
     */
    public function preview(DailyReport $dailyReport)
    {
        $this->authorize('view', $dailyReport);
        return view('client.laporan.preview', ['report' => $dailyReport]);
    }

    /**
     * Update meta data (judul, logo) dari preview.
     */
    public function updatePreview(Request $request, DailyReport $dailyReport)
    {
        $this->authorize('update', $dailyReport);
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'logo'  => 'nullable|image|max:1024', // batas 1MB
        ]);
        $data = $dailyReport->data ?? [];
        // Pastikan struktur meta tersedia
        if (!isset($data['meta'])) {
            $data['meta'] = [];
        }
        $data['meta']['title'] = $validated['title'] ?? ($data['meta']['title'] ?? '');
        if ($request->hasFile('logo')) {
            // Fitur upload logo hanya untuk pengguna berlangganan
            if (!Auth::user()->hasActiveSubscription()) {
                return back()->with('error', 'Fitur upload logo hanya tersedia untuk pengguna berlangganan.');
            }
            $file = $request->file('logo');
            // Hitung hash untuk deduplikasi
            $hash = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $hashedName = $hash . '.' . $extension;
            $disk = Storage::disk('public');
            if ($disk->exists('logos/' . $hashedName)) {
                $path = 'logos/' . $hashedName;
            } else {
                $path = $file->storeAs('logos', $hashedName, 'public');
            }
            $data['meta']['logo'] = $path;
        }
        $dailyReport->data = $data;
        $dailyReport->save();
        return back()->with('success', 'Informasi laporan diperbarui.');
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
