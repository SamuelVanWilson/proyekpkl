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
            ['key'=>'title','label'=>'Judul Laporan','type'=>'text','readonly'=>true],
            ['key'=>'tanggal_raw','label'=>'Tanggal Laporan','type'=>'date','readonly'=>true],
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
        // Keep only text/number/date, reject others
        $allowed = ['text','number','date'];
        $normalized = [];
        foreach ($fields as $f) {
            $type = in_array($f['type'] ?? 'text', $allowed) ? $f['type'] : 'text';
            $key  = preg_replace('/[^a-z0-9_]+/','_', strtolower($f['key'] ?? ''));
            $label= trim($f['label'] ?? '');
            if ($key && $label) {
                $normalized[] = ['key'=>$key,'label'=>$label,'type'=>$type,'readonly'=>false];
            }
        }

        // Always prepend default mandatory fields
        $schema = array_merge([
            ['key'=>'title','label'=>'Judul Laporan','type'=>'text','readonly'=>true],
            ['key'=>'tanggal_raw','label'=>'Tanggal Laporan','type'=>'date','readonly'=>true],
        ], $normalized);

        $data = $dailyReport->data ?? [];
        $data['detail_schema'] = $schema;
        $dailyReport->data = $data;
        $dailyReport->save();

        return redirect()->route('client.laporan.simple.config.edit', $dailyReport->id)
            ->with('success','Konfigurasi tersimpan dan hanya berlaku untuk laporan ini.');
    }
}
