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
        $reports = DailyReport::where('user_id', $user->id)
                              ->orderBy('tanggal', 'desc')
                              ->paginate(15);
        return view('client.laporan.histori', compact('reports'));
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
        if (!empty($dailyReport->data)) {
            return view('client.laporan.biasa', ['reportId' => $dailyReport->id]);
        }
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
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            'logo'       => 'nullable|image|max:1024',
            'header_row' => 'nullable|integer|min:1',
            'detail_pos' => 'nullable|in:top,bottom',
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
            if (!Auth::user()->hasActiveSubscription()) {
                // Jangan redirect ke halaman berlangganan, hanya tampilkan pesan
                return back()->with('error', 'Fitur upload logo hanya tersedia untuk pengguna berlangganan.');
            }
            $file = $request->file('logo');
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
            $targetRoute = Auth::user()->hasActiveSubscription() ? 'laporan.advanced' : 'laporan.harian';
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
}