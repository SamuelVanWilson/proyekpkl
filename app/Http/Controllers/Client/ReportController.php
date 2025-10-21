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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller untuk halaman laporan pengguna (client).
 * Menyediakan fungsi untuk menampilkan form laporan (biasa & advanced), histori,
 * preview PDF, update meta data (judul, logo), download PDF, dan form builder.
 */
class ReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Tampilkan halaman laporan harian dengan komponen Livewire Harian.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function harian()
    {
        return view('client.laporan.harian');
    }

    /**
     * Tampilkan halaman laporan sederhana (biasa) dengan komponen SimpleTable.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function biasa()
    {
        return view('client.laporan.biasa');
    }

    /**
     * Tampilkan halaman laporan advanced. Hanya untuk pengguna dengan langganan aktif.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function advanced()
    {
        return view('client.laporan.advanced');
    }

    /**
     * Tampilkan halaman histori laporan.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function histori()
    {
        $user = Auth::user();
        // Tentukan laporan biasa terbaru yang diizinkan untuk user non‑langganan.
        $unlockedSimpleIds = [];
        if (!$user->hasActiveSubscription()) {
            $unlockedSimpleIds = $this->getUnlockedSimpleReportIds($user);
        }
        $reports = DailyReport::where('user_id', $user->id)
                              ->orderByDesc('updated_at')
                              ->paginate(8);
        return view('client.laporan.histori', [
            'reports' => $reports,
            'unlockedSimpleIds' => $unlockedSimpleIds,
        ]);
    }

    /**
     * Tampilkan preview PDF dari laporan.
     *
     * @param \App\Models\DailyReport $dailyReport
     * @return \Illuminate\Contracts\View\View
     */
    public function previewPdf(DailyReport $dailyReport)
    {
        $this->authorize('view', $dailyReport);

        // Batasi akses: jika laporan advanced dan langganan tidak aktif, alihkan pengguna.
        if (!Auth::user()->hasActiveSubscription() && !empty($dailyReport->data) && isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap'])) {
            return redirect()->route('client.laporan.harian')
                ->with('error', 'Masa langganan Anda telah berakhir. Silakan perpanjang untuk mengakses laporan advanced.');
        }
        // Batasi akses laporan biasa untuk user non‑langganan jika laporan ini bukan salah satu dari dua laporan biasa terbaru.
        $user = Auth::user();
        $isSimple = empty($dailyReport->data) || !(isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap']));
        if ($isSimple && !$user->hasActiveSubscription()) {
            $unlockedSimpleIds = $this->getUnlockedSimpleReportIds($user);
            if (!in_array($dailyReport->id, $unlockedSimpleIds)) {
                return redirect()->route('client.laporan.harian')
                    ->with('error', 'Anda hanya dapat mengakses 2 laporan biasa terbaru. Silakan hapus laporan lama atau berlangganan untuk akses penuh.');
            }
        }
        $data = [
            'report' => $dailyReport,
        ];
        return view('client.laporan.pdf_template', $data);
    }

    /**
     * Tampilkan form edit laporan sederhana. Hanya untuk laporan biasa (data json) yang ada.
     *
     * @param \App\Models\DailyReport $dailyReport
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function edit(DailyReport $dailyReport)
    {
        $this->authorize('update', $dailyReport);

        // Jika laporan merupakan laporan advanced dan langganan pengguna sudah tidak aktif,
        // blok akses dan arahkan kembali ke halaman laporan biasa dengan pesan.
        // Laporan advanced ditandai dengan adanya struktur 'rincian' dan 'rekap' pada field data.
        if (!Auth::user()->hasActiveSubscription() && !empty($dailyReport->data) && isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap'])) {
            return redirect()->route('client.laporan.harian')
                ->with('error', 'Masa langganan Anda telah berakhir. Silakan perpanjang untuk mengakses laporan advanced.');
        }
        // Tambahan logika: jika laporan biasa tetapi user non‑langganan memiliki lebih dari dua laporan biasa,
        // izinkan edit hanya dua laporan biasa terbaru; laporan lain dikunci.
        $user = Auth::user();
        // Laporan dianggap sederhana jika tidak memiliki struktur 'rincian' dan 'rekap' atau data kosong
        $isSimple = empty($dailyReport->data) || !(isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap']));
        if ($isSimple && !$user->hasActiveSubscription()) {
            $unlockedSimpleIds = $this->getUnlockedSimpleReportIds($user);
            if (!in_array($dailyReport->id, $unlockedSimpleIds)) {
                return redirect()->route('client.laporan.harian')
                    ->with('error', 'Anda hanya dapat mengakses 2 laporan biasa terbaru. Silakan hapus laporan lama atau berlangganan untuk akses penuh.');
            }
        }
        if (!empty($dailyReport->data)) {
            // Jika laporan memiliki rincian & rekap maka ini laporan advanced
            if (isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap'])) {
                return view('client.laporan.advanced', ['reportId' => $dailyReport->id]);
            }
            // Selain itu, anggap sebagai laporan biasa
            return view('client.laporan.biasa', ['reportId' => $dailyReport->id]);
        }
        // Default: redirect dengan pesan jika data kosong
        return redirect()->route('client.laporan.histori')->with('message', 'Edit laporan advanced belum tersedia.');
    }

    /**
     * Hapus laporan dari database.
     *
     * @param \App\Models\DailyReport $dailyReport
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(DailyReport $dailyReport)
    {
        $this->authorize('delete', $dailyReport);
        $dailyReport->delete();
        return redirect()->route('client.laporan.histori')->with('success', 'Laporan berhasil dihapus.');
    }

    /**
     * Download laporan sebagai PDF menggunakan DomPDF.
     *
     * @param \App\Models\DailyReport $dailyReport
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadPdf(DailyReport $dailyReport)
    {
        $this->authorize('view', $dailyReport);

        // Batasi akses: jika laporan advanced dan langganan tidak aktif, alihkan pengguna.
        if (!Auth::user()->hasActiveSubscription() && !empty($dailyReport->data) && isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap'])) {
            return redirect()->route('client.laporan.harian')
                ->with('error', 'Masa langganan Anda telah berakhir. Silakan perpanjang untuk mengakses laporan advanced.');
        }
        // Batasi akses laporan biasa untuk user non‑langganan jika laporan ini bukan salah satu dari dua laporan biasa terbaru.
        $user = Auth::user();
        $isSimple = empty($dailyReport->data) || !(isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap']));
        if ($isSimple && !$user->hasActiveSubscription()) {
            $unlockedSimpleIds = $this->getUnlockedSimpleReportIds($user);
            if (!in_array($dailyReport->id, $unlockedSimpleIds)) {
                return redirect()->route('client.laporan.harian')
                    ->with('error', 'Anda hanya dapat mengakses 2 laporan biasa terbaru. Silakan hapus laporan lama atau berlangganan untuk akses penuh.');
            }
        }
        $user = Auth::user();
        // Pastikan judul laporan terisi sebelum export
        $reportTitle = $dailyReport->data['meta']['title'] ?? null;
        if (empty($reportTitle)) {
            return back()->with('error', 'Judul laporan harus diisi sebelum mengunduh PDF. Silakan isi judul pada halaman laporan atau di preview.');
        }
        // Batasi jumlah export per hari untuk user free
        if (!$user->hasActiveSubscription()) {
            $exportCountToday = PdfExport::where('user_id', $user->id)
                ->whereDate('exported_at', today())
                ->count();
            if ($exportCountToday >= 3) {
                return back()->with('error', 'Limit export PDF gratis (3x per hari) telah tercapai. Silakan berlangganan untuk export tanpa batas.');
            }
        }
        $pdf = Pdf::loadView('client.laporan.pdf_template', ['report' => $dailyReport]);
        $fileName = 'laporan-' . $dailyReport->tanggal . '-' . now()->timestamp . '.pdf';
        // Rekam aktivitas export
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
        if (\Illuminate\Support\Facades\Schema::hasColumn('pdf_exports', 'daily_report_id')) {
            $exportData['daily_report_id'] = $dailyReport->id;
        }
        PdfExport::create($exportData);
        return $pdf->download($fileName);
    }

    /**
     * Tampilkan halaman preview PDF dengan opsi meta (judul, logo, header row, posisi detail).
     *
     * @param \App\Models\DailyReport $dailyReport
     * @return \Illuminate\Contracts\View\View
     */
    public function preview(DailyReport $dailyReport)
    {
        $this->authorize('view', $dailyReport);

        // Batasi akses: jika laporan advanced dan langganan tidak aktif, alihkan pengguna.
        if (!Auth::user()->hasActiveSubscription() && !empty($dailyReport->data) && isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap'])) {
            return redirect()->route('client.laporan.harian')
                ->with('error', 'Masa langganan Anda telah berakhir. Silakan perpanjang untuk mengakses laporan advanced.');
        }
        // Batasi akses laporan biasa untuk user non‑langganan jika laporan ini bukan salah satu dari dua laporan biasa terbaru.
        $user = Auth::user();
        $isSimple = empty($dailyReport->data) || !(isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap']));
        if ($isSimple && !$user->hasActiveSubscription()) {
            $unlockedSimpleIds = $this->getUnlockedSimpleReportIds($user);
            if (!in_array($dailyReport->id, $unlockedSimpleIds)) {
                return redirect()->route('client.laporan.harian')
                    ->with('error', 'Anda hanya dapat mengakses 2 laporan biasa terbaru. Silakan hapus laporan lama atau berlangganan untuk akses penuh.');
            }
        }
        return view('client.laporan.preview', ['report' => $dailyReport]);
    }

    /**
     * Update meta data (judul, logo, baris header, posisi detail) dari halaman preview.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DailyReport $dailyReport
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePreview(Request $request, DailyReport $dailyReport)
    {
        $this->authorize('update', $dailyReport);

        // Batasi akses: jika laporan advanced dan langganan tidak aktif, alihkan pengguna.
        if (!Auth::user()->hasActiveSubscription() && !empty($dailyReport->data) && isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap'])) {
            return redirect()->route('client.laporan.harian')
                ->with('error', 'Masa langganan Anda telah berakhir. Silakan perpanjang untuk mengakses laporan advanced.');
        }
        // Batasi akses laporan biasa untuk user non‑langganan jika laporan ini bukan salah satu dari dua laporan biasa terbaru.
        $user = Auth::user();
        $isSimple = empty($dailyReport->data) || !(isset($dailyReport->data['rincian']) && isset($dailyReport->data['rekap']));
        if ($isSimple && !$user->hasActiveSubscription()) {
            $unlockedSimpleIds = $this->getUnlockedSimpleReportIds($user);
            if (!in_array($dailyReport->id, $unlockedSimpleIds)) {
                return redirect()->route('client.laporan.harian')
                    ->with('error', 'Anda hanya dapat mengakses 2 laporan biasa terbaru. Silakan hapus laporan lama atau berlangganan untuk akses penuh.');
            }
        }
        // Validasi masukan meta. Judul boleh kosong, logo harus berformat JPG/JPEG/PNG agar pengguna mendapat notifikasi
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            // Terima hanya file gambar dengan ekstensi jpg, jpeg atau png. Gunakan rule file+mimes agar validasi jelas.
            'logo'       => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'header_row' => 'nullable|integer|min:1',
            'detail_pos' => 'nullable|in:top,bottom',
        ], [
            'logo.mimes' => 'Format logo harus JPG, JPEG, atau PNG.',
            'logo.max'   => 'Ukuran logo maksimal 5 MB.',
        ]);
        $data = $dailyReport->data ?? [];
        if (!isset($data['meta'])) {
            $data['meta'] = [];
        }
        // Simpan judul
        $data['meta']['title'] = $validated['title'] ?? ($data['meta']['title'] ?? '');
        // Simpan pilihan header row
        if (!empty($validated['header_row'])) {
            $data['meta']['header_row'] = (int) $validated['header_row'];
        }
        // Simpan posisi detail
        if (!empty($validated['detail_pos'])) {
            $data['meta']['detail_pos'] = $validated['detail_pos'];
        }
        // Tangani upload logo
        if ($request->hasFile('logo')) {
            // Jika user tidak berlangganan, tampilkan pesan error dan jangan proses upload
            if (!Auth::user()->hasActiveSubscription()) {
                return back()->with('error', 'Upload logo hanya tersedia bagi pengguna berlangganan.');
            }
            $file = $request->file('logo');
            // Hash nama file untuk mencegah tabrakan nama dan menyimpan ke penyimpanan publik
            $hash      = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $hashedName = $hash . '.' . $extension;
            $disk = Storage::disk('public');
            if ($disk->exists('logos/' . $hashedName)) {
                $path = 'logos/' . $hashedName;
            } else {
                $path = $file->storeAs('logos', $hashedName, 'public');
            }
            // Simpan path logo ke meta. Nantinya template PDF akan membaca dari storage path.
            $data['meta']['logo'] = $path;
        }
        $dailyReport->data = $data;
        $dailyReport->save();
        return redirect()->route('client.laporan.preview', $dailyReport)->with('success', 'Informasi laporan diperbarui.');
    }

    /**
     * Simpan konfigurasi form builder (rincian & rekap) untuk user atau admin.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User|null $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveFormBuilder(Request $request, User $user = null)
    {
        $validated = $request->validate([
            'rincian'            => 'sometimes|array',
            'rincian.*.label'    => 'required_with:rincian|string',
            'rincian.*.type'     => 'required_with:rincian|string',
            'rekap'              => 'sometimes|array',
            'rekap.*.label'      => 'required_with:rekap|string',
            'rekap.*.type'       => 'required_with:rekap|string',
            'rekap.*.formula'    => 'nullable|string',
            'rekap.*.default_value' => 'nullable|string',
            'rekap.*.readonly'   => 'sometimes|boolean',
            // Kolom tambahan: menandai apakah field rekap digunakan sebagai data grafik
            'rekap.*.used_for_chart' => 'sometimes|boolean',
        ]);
        $processedColumns = [];
        // Proses rincian
        if (!empty($validated['rincian'])) {
            foreach ($validated['rincian'] as $col) {
                $processedColumns['rincian'][] = [
                    'name'  => \Illuminate\Support\Str::slug($col['label'], '_'),
                    'label' => $col['label'],
                    'type'  => $col['type'],
                ];
            }
        } else {
            $processedColumns['rincian'] = [];
        }
        // Proses rekap
        if (!empty($validated['rekap'])) {
            foreach ($validated['rekap'] as $col) {
                $processedColumns['rekap'][] = [
                    'name'         => \Illuminate\Support\Str::slug($col['label'], '_'),
                    'label'        => $col['label'],
                    'type'         => $col['type'],
                    'formula'      => $col['formula'] ?? null,
                    'default_value'=> $col['default_value'] ?? null,
                    'readonly'     => !empty($col['readonly']),
                    // Simpan flag "used_for_chart" agar grafik hanya mengambil kolom yang dipilih
                    'used_for_chart'=> !empty($col['used_for_chart']),
                ];
            }
        } else {
            $processedColumns['rekap'] = [];
        }
        // Tentukan user_id untuk konfigurasi
        $userId = $user ? $user->id : Auth::id();
        TableConfiguration::updateOrCreate(
            ['user_id' => $userId, 'table_name' => 'daily_reports'],
            ['columns' => $processedColumns]
        );
        if ($user) {
            return redirect()->route('admin.users.index')->with('success', 'Konfigurasi form untuk ' . $user->name . ' berhasil disimpan.');
        } else {
            // Setelah menyimpan konfigurasi sebagai pengguna biasa/premium, arahkan ke halaman laporan sesuai status langganan.
            // Gunakan nama rute yang benar tanpa prefix "client." karena rute yang didefinisikan di web.php adalah lapiran.harian dan lapiran.advanced.
            $targetRoute = Auth::user()->hasActiveSubscription() ? 'client.laporan.advanced' : 'client.laporan.harian';
            return redirect()->route($targetRoute)->with('success', 'Struktur laporan Anda berhasil diperbarui!');
        }
    }

    /**
     * Tampilkan form builder untuk konfigurasi laporan.
     *
     * @param \App\Models\User|null $user
     * @return \Illuminate\Contracts\View\View
     */
    public function showFormBuilder(User $user = null)
    {
        $userId = $user ? $user->id : Auth::id();
        $config = TableConfiguration::firstOrNew(
            ['user_id' => $userId, 'table_name' => 'daily_reports'],
            ['columns' => ['rincian' => [], 'rekap' => []]]
        );
        $columns = $config->columns;
        // Pastikan setiap kolom memiliki label
        if (!empty($columns['rincian'])) {
            foreach ($columns['rincian'] as $key => $col) {
                if (empty($col['label'])) {
                    $columns['rincian'][$key]['label'] = ucwords(str_replace('_', ' ', $col['name']));
                }
            }
        }
        if (!empty($columns['rekap'])) {
            foreach ($columns['rekap'] as $key => $col) {
                if (empty($col['label'])) {
                    $columns['rekap'][$key]['label'] = ucwords(str_replace('_', ' ', $col['name']));
                }
            }
        }
        if ($user) {
            return view('admin.users.form-builder', compact('user', 'config', 'columns'));
        }
        return view('client.laporan.form-builder', compact('columns'));
    }

    /**
     * Dapatkan ID laporan sederhana (biasa) yang masih dapat diakses oleh pengguna non-langganan.
     * Pengguna non-langganan hanya diperbolehkan mengakses dua laporan biasa terbaru.
     * Jika pengguna masih berlangganan, maka semua laporan biasa dianggap terbuka.
     *
     * @param  \App\Models\User  $user
     * @return array<int>
     */
    private function getUnlockedSimpleReportIds(User $user): array
    {
        // Jika pengguna memiliki langganan aktif, tidak ada pembatasan: semua laporan sederhana terbuka.
        if ($user->hasActiveSubscription()) {
            return [];
        }
        // Ambil semua laporan pengguna, urutkan berdasarkan tanggal terbaru ke terlama, lalu filter hanya laporan sederhana.
        $simpleReports = DailyReport::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->filter(function ($rep) {
                $data = $rep->data ?? [];
                // Laporan dianggap sederhana jika tidak memiliki key 'rincian' dan 'rekap'.
                return !(isset($data['rincian']) && isset($data['rekap']));
            })
            ->take(2);
        return $simpleReports->pluck('id')->toArray();
    }
}
