<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;
use App\Models\TableConfiguration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;

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
            // Konfigurasi default
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
        
        if ($this->report->exists && isset($this->report->data['rincian'])) {
            $this->rincian = $this->report->data['rincian'];
            $this->rekap = $this->report->data['rekap'];
        } else {
            $this->rincian = []; 
            for ($i = 0; $i < 10; $i++) {
                $this->tambahBarisRincian(false);
            }
            
            foreach($this->configRekap as $field) {
                $this->rekap[$field['name']] = ($field['type'] == 'date') ? now()->format('Y-m-d') : '';
            }
        }
        $this->hitungUlang();
    }

    public function tambahBarisRincian($recalculate = true)
    {
        $newRow = [];
        foreach ($this->configRincian as $col) {
            $newRow[$col['name']] = '';
        }
        $this->rincian[] = $newRow;
        
        if ($recalculate) {
            $this->hitungUlang();
        }
    }

    public function hapusBarisRincian($index)
    {
        unset($this->rincian[$index]);
        $this->rincian = array_values($this->rincian);
        $this->hitungUlang();
    }
    
    public function updated($name, $value)
    {
        $this->hitungUlang();
    }

    // --- MESIN RUMUS BARU YANG CANGGIH ---
    public function hitungUlang()
    {
        foreach ($this->configRekap as $field) {
            if (!empty($field['formula'])) {
                $formula = $field['formula'];
                
                // 1. Evaluasi fungsi agregat seperti SUM()
                preg_match_all('/SUM\((.*?)\)/', $formula, $matches);
                foreach ($matches[1] as $colToSum) {
                    $sum = collect($this->rincian)->sum(fn($item) => (float)($item[trim($colToSum)] ?? 0));
                    $formula = str_replace("SUM(".trim($colToSum).")", $sum, $formula);
                }

                // 2. Ganti nama kolom rekapitulasi dengan nilainya
                foreach ($this->rekap as $key => $value) {
                    if (is_string($key)) {
                        $numericValue = is_numeric($value) ? (float) $value : 0;
                        $formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/', (string)$numericValue, $formula);
                    }
                }

                // 3. Evaluasi ekspresi matematika yang aman
                $this->rekap[$field['name']] = $this->evaluateFormula($formula);
            }
        }
    }

    private function evaluateFormula($formula)
    {
        try {
            $sanitizedFormula = preg_replace('/[^-0-9\.\+\*\/ \(\)]/', '', $formula);
            if (empty($sanitizedFormula) || !preg_match('/[0-9]/', $sanitizedFormula)) {
                return 0;
            }
            return eval("return {$sanitizedFormula};");
        } catch (\Throwable $e) {
            Log::error("Formula evaluation error: " . $e->getMessage() . " | Original Formula: " . $formula);
            return 0;
        }
    }

    // --- LOGIKA PENYIMPANAN DATA YANG BENAR ---
    public function simpanLaporan()
    {
        // Gabungkan semua data menjadi satu array
        $dataToStore = [
            'rekap' => $this->rekap,
            'rincian' => $this->rincian,
        ];

        DB::transaction(function () use ($dataToStore) {
            // Simpan atau perbarui data utama di daily_reports
            $this->report->data = $dataToStore;
            $this->report->save();

            // Hapus rincian lama dari tabel 'barangs'
            Barang::where('daily_report_id', $this->report->id)->delete();

            // Simpan setiap baris rincian baru ke tabel 'barangs'
            foreach ($this->rincian as $item) {
                // Hanya simpan jika baris tidak kosong
                if (collect($item)->filter()->isNotEmpty()) {
                    Barang::create([
                        'user_id' => Auth::id(),
                        'daily_report_id' => $this->report->id,
                        'data' => $item,
                    ]);
                }
            }
        });

        session()->flash('success', 'Laporan hari ini berhasil disimpan/diperbarui!');
    }

    public function render()
    {
        return view('livewire.laporan.harian');
    }
}
