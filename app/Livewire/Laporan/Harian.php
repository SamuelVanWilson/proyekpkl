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
    public $selectedRowIndex = null; // Properti baru untuk baris terpilih

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

    // FUNGSI BARU: Untuk memilih baris
    public function selectRow($index)
    {
        // Jika baris yang sama diklik lagi, batalkan pilihan. Jika tidak, pilih baris baru.
        $this->selectedRowIndex = $this->selectedRowIndex === $index ? null : $index;
    }

    // PERUBAHAN: Method hapus sekarang berdasarkan baris yang dipilih
    public function hapusBarisTerpilih()
    {
        if ($this->selectedRowIndex !== null && isset($this->rincian[$this->selectedRowIndex])) {
            unset($this->rincian[$this->selectedRowIndex]);
            $this->rincian = array_values($this->rincian);
            $this->selectedRowIndex = null; // Reset pilihan setelah menghapus
            $this->hitungUlang();
        }
    }

    public function updated($name, $value)
    {
        $this->hitungUlang();
    }

    // --- PERUBAHAN PADA MESIN RUMUS ---
    public function hitungUlang()
    {
        foreach ($this->configRekap as $field) {
            if (!empty($field['formula'])) {
                $formula = $field['formula'];

                // 1. Evaluasi fungsi agregat SUM()
                preg_match_all('/SUM\((.*?)\)/', $formula, $sumMatches);
                foreach ($sumMatches[1] as $colToSum) {
                    $sum = collect($this->rincian)->sum(fn($item) => (float)($item[trim($colToSum)] ?? 0));
                    $formula = str_replace("SUM(".trim($colToSum).")", $sum, $formula);
                }

                // 2. IMPLEMENTASI BARU: Evaluasi fungsi SUBT(nilai_awal, kolom)
                preg_match_all('/SUBT\(([^,]+),\s*([^)]+)\)/', $formula, $subtMatches, PREG_SET_ORDER);
                foreach ($subtMatches as $match) {
                    $initialValue = (float)($this->rekap[trim($match[1])] ?? (is_numeric($match[1]) ? $match[1] : 0));
                    $colToSubtract = trim($match[2]);
                    $sumOfSubtractColumn = collect($this->rincian)->sum(fn($item) => (float)($item[$colToSubtract] ?? 0));
                    $result = $initialValue - $sumOfSubtractColumn;
                    $formula = str_replace($match[0], $result, $formula);
                }

                // 3. Ganti nama kolom rekapitulasi dengan nilainya
                foreach ($this->rekap as $key => $value) {
                    if (is_string($key)) {
                        $numericValue = is_numeric($value) ? (float) $value : 0;
                        $formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/', (string)$numericValue, $formula);
                    }
                }

                // 4. Evaluasi ekspresi matematika yang aman
                $this->rekap[$field['name']] = $this->evaluateFormula($formula);
            }
        }
    }

    private function evaluateFormula($formula)
    {
        try {
            // Hanya izinkan angka, operator, dan tanda kurung untuk keamanan
            $sanitizedFormula = preg_replace('/[^-0-9\.\+\*\/ \(\)]/', '', $formula);
            if (empty($sanitizedFormula) || !preg_match('/[0-9]/', $sanitizedFormula)) {
                return 0;
            }
            // Menggunakan @ untuk menekan error jika formula tidak valid (misal: "5 * ")
            return @eval("return {$sanitizedFormula};") ?? 0;
        } catch (\Throwable $e) {
            Log::error("Formula evaluation error: " . $e->getMessage() . " | Original Formula: " . $formula);
            return 0; // Kembalikan 0 jika ada error
        }
    }

    public function simpanLaporan()
    {
        // ... (Logika simpan laporan tidak berubah)
        $dataToStore = [
            'rekap' => $this->rekap,
            'rincian' => array_filter($this->rincian, fn($row) => collect($row)->filter()->isNotEmpty()) // Hanya simpan baris yg terisi
        ];

        DB::transaction(function () use ($dataToStore) {
            $this->report->data = $dataToStore;
            $this->report->save();
            Barang::where('daily_report_id', $this->report->id)->delete();
            foreach ($dataToStore['rincian'] as $item) {
                Barang::create([
                    'user_id' => Auth::id(),
                    'daily_report_id' => $this->report->id,
                    'data' => $item,
                ]);
            }
        });

        session()->flash('success', 'Laporan hari ini berhasil disimpan/diperbarui!');
    }

    public function render()
    {
        return view('livewire.laporan.harian');
    }
}
