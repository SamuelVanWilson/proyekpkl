<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyReport;

/**
 * Controller untuk konfigurasi tabel laporan sederhana (SimpleTable).
 * Setiap laporan memiliki konfigurasi kolom sendiri sehingga halaman baru
 * tidak mewarisi konfigurasi dari laporan lain.
 */
class SimpleTableConfigController extends Controller
{
    /**
     * Tampilkan formulir konfigurasi untuk laporan sederhana.
     *
     * @param \App\Models\DailyReport $report
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(DailyReport $report)
    {
        // Pastikan pengguna adalah pemilik laporan
        if ($report->user_id !== auth()->id()) { abort(403); }

        // Ambil konfigurasi dari data report jika ada
        $columns = $report->data['simple_config']['columns'] ?? null;
        $columnCount = is_array($columns) ? count($columns) : 5;

        return view('client.laporan.simple-table-config', [
            'report' => $report,
            'columnCount' => $columnCount,
        ]);
    }

    /**
     * Simpan konfigurasi kolom untuk laporan sederhana.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DailyReport $report
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, DailyReport $report)
    {
        if ($report->user_id !== auth()->id()) { abort(403); }

        $validated = $request->validate([
            'column_count' => ['required','integer','min:1','max:26'],
        ]);
        $count = (int) $validated['column_count'];
        $letters = range('A', 'Z');
        $newColumns = array_slice($letters, 0, $count);

        // Perbarui konfigurasi simple
        $data = $report->data ?? [];
        $data['simple_config']['columns'] = $newColumns;

        // Perbarui nilai-nilai baris agar sesuai jumlah kolom baru
        $rows = $data['rows'] ?? [];
        $rebuilt = [];
        $minRows = max(count($rows), 6);
        for ($i = 0; $i < $minRows; $i++) {
            $rebuilt[$i] = [];
            foreach ($newColumns as $col) {
                $rebuilt[$i][$col] = (string) ($rows[$i][$col] ?? '');
            }
        }
        $data['rows'] = $rebuilt;
        $data['columns'] = $newColumns;

        $report->data = $data;
        $report->save();

        return redirect()->route('client.laporan.harian')
            ->with('success', 'Konfigurasi tabel disimpan.');
    }
}