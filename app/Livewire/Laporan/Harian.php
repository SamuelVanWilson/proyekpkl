<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;
use App\Models\TableConfiguration;
use Illuminate\Support\Facades\Log;

class Harian extends Component
{
    public $report;
    public $rincian = [];
    public $rekap = [];
    public $configRincian = [];
    public $configRekap = [];

    public function mount()
    {
        $this->loadConfig();
        $this->loadOrCreateReport();
    }

    public function loadConfig()
    {
        $config = TableConfiguration::where('user_id', Auth::id())
                                    ->where('table_name', 'daily_reports')
                                    ->first();

        if ($config && !empty($config->columns)) {
            $this->configRincian = $config->columns['rincian'] ?? [];
            $this->configRekap = $config->columns['rekap'] ?? [];
        } else {
            // Konfigurasi default jika admin belum mengatur
            $this->configRincian = [['name' => 'total', 'label' => 'Total', 'type' => 'number']];
            $this->configRekap = [
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'formula' => null],
                ['name' => 'lokasi', 'label' => 'Lokasi', 'type' => 'text', 'formula' => null],
                ['name' => 'total_bruto', 'label' => 'Total Bruto', 'type' => 'number', 'formula' => 'SUM(total)'],
            ];
        }
    }

    public function loadOrCreateReport()
    {
        $this->report = DailyReport::firstOrNew([
            'user_id' => Auth::id(),
            'tanggal' => now()->toDateString(),
        ]);

        $this->rincian = $this->report->data['rincian'] ?? [];
        $this->rekap = $this->report->data['rekap'] ?? [];

        // Inisialisasi data kosong jika laporan baru
        if (!$this->report->exists) {
            $this->tambahBarisRincian();
            foreach($this->configRekap as $field) {
                $this->rekap[$field['name']] = ($field['type'] == 'date') ? now()->format('Y-m-d') : '';
            }
        }
        $this->hitungUlang();
    }

    public function tambahBarisRincian()
    {
        $newRow = [];
        foreach ($this->configRincian as $col) {
            $newRow[$col['name']] = '';
        }
        $this->rincian[] = $newRow;
    }

    public function hapusBarisRincian($index)
    {
        unset($this->rincian[$index]);
        $this->rincian = array_values($this->rincian);
        $this->updated();
    }

    public function updated()
    {
        $this->hitungUlang();
    }

    public function hitungUlang()
    {
        foreach ($this->configRekap as $field) {
            if (!empty($field['formula'])) {
                $formula = $field['formula'];

                // Evaluasi SUM(nama_kolom)
                preg_match_all('/SUM\((.*?)\)/', $formula, $matches);
                foreach ($matches[1] as $colToSum) {
                    $sum = collect($this->rincian)->sum(function($item) use ($colToSum) {
                        return is_numeric($item[$colToSum] ?? 0) ? $item[$colToSum] : 0;
                    });
                    $formula = str_replace("SUM($colToSum)", $sum, $formula);
                }

                // Ganti nama kolom lain dengan nilainya
                foreach ($this->rekap as $key => $value) {
                    if (is_numeric($value)) {
                        $formula = str_replace($key, $value, $formula);
                    }
                }

                // Evaluasi ekspresi matematika sederhana
                try {
                    // Hapus karakter non-matematika yang tersisa
                    $sanitizedFormula = preg_replace('/[^0-9\.\+\-\*\/ \(\)]/', '', $formula);
                    if (!empty($sanitizedFormula)) {
                         $this->rekap[$field['name']] = eval("return {$sanitizedFormula};");
                    }
                } catch (\Throwable $e) {
                    // Abaikan jika formula tidak valid
                    Log::error("Formula evaluation error: " . $e->getMessage());
                }
            }
        }
    }

    public function simpanLaporan()
    {
        $this->report->data = [
            'rekap' => $this->rekap,
            'rincian' => $this->rincian,
        ];
        $this->report->save();

        session()->flash('success', 'Laporan hari ini berhasil disimpan/diperbarui!');
    }

    public function render()
    {
        return view('livewire.laporan.harian');
    }
}
