<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;

class SimpleTableConfigController extends Controller
{
    public function edit(DailyReport $dailyReport)
    {
        // Only owner can edit
        abort_unless($dailyReport->user_id === Auth::id(), 403);

        $data = $dailyReport->data ?? [];
        $schema = $data['detail_schema'] ?? [
            ['key' => 'title',       'label' => 'Judul Laporan',   'type' => 'text', 'readonly' => true],
            ['key' => 'tanggal_raw', 'label' => 'Tanggal Laporan', 'type' => 'date', 'readonly' => true],
        ];

        return view('client.laporan.simple-config', [
            'report' => $dailyReport,
            'schema' => $schema,
        ]);
    }

    public function update(Request $request, DailyReport $dailyReport)
    {
        abort_unless($dailyReport->user_id === Auth::id(), 403);

        $fields = $request->input('fields', []);
        $allowed = ['text', 'number', 'date'];
        $normalized = [];
        // Proses setiap field dari input. Hanya gunakan label & type, key akan dibuat otomatis dari label
        foreach ($fields as $f) {
            $label = trim($f['label'] ?? '');
            if ($label === '') {
                continue;
            }
            $type = in_array($f['type'] ?? 'text', $allowed) ? $f['type'] : 'text';
            // Generate slug for key from label
            $key = \Illuminate\Support\Str::slug($label, '_');
            // Hindari key yang sama dengan title atau tanggal_raw
            if (in_array($key, ['title', 'tanggal_raw'])) {
                // Tambahkan suffix untuk mencegah konflik
                $key .= '_field';
            }
            $normalized[] = [
                'key'      => $key,
                'label'    => $label,
                'type'     => $type,
                'readonly' => false,
            ];
        }
        // Selalu prepend field default (Judul & Tanggal) dengan readonly
        $schema = array_merge([
            ['key' => 'title',       'label' => 'Judul Laporan',   'type' => 'text', 'readonly' => true],
            ['key' => 'tanggal_raw', 'label' => 'Tanggal Laporan', 'type' => 'date', 'readonly' => true],
        ], $normalized);
        // Simpan ke data laporan
        $data = $dailyReport->data ?? [];
        $data['detail_schema'] = $schema;
        $dailyReport->data = $data;
        $dailyReport->save();

        return redirect()->route('client.laporan.edit', $dailyReport)
            ->with('success', 'Konfigurasi tersimpan dan hanya berlaku untuk laporan ini.');
    }
}
