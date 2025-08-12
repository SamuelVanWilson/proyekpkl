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
    public $selectedRowIndex = null;

    public function mount()
    {
        $this->loadConfig();
        $this->loadOrCreateReport();
    }

    private function parseNumber($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }
        // 1. Hapus semua karakter kecuali angka, koma, dan titik.
        $cleaned = preg_replace('/[^\d,.]/', '', (string) $value);
        // 2. Ganti koma desimal gaya Eropa dengan titik.
        $cleaned = str_replace(',', '.', $cleaned);
        // 3. Hapus titik pemisah ribuan.
        $cleaned = str_replace('.', '', substr($cleaned, 0, -3)) . substr($cleaned, -3);

        return (float) $cleaned;
    }

    public function loadConfig()
    {
        $config = \App\Models\TableConfiguration::where('user_id', \Illuminate\Support\Facades\Auth::id())
                                    ->where('table_name', 'daily_reports')
                                    ->first();

        if ($config && !empty($config->columns)) {
            $this->configRincian = $config->columns['rincian'] ?? [];
            $this->configRekap = $config->columns['rekap'] ?? [];
        } else {
            // Konfigurasi default jika tidak ada
            $this->configRincian = [['name' => 'total', 'label' => 'Total', 'type' => 'number']];
            $this->configRekap = [
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'formula' => null, 'readonly' => false],
                ['name' => 'lokasi', 'label' => 'Lokasi', 'type' => 'text', 'formula' => null, 'readonly' => false],
                ['name' => 'total_bruto', 'label' => 'Total Bruto', 'type' => 'number', 'formula' => 'SUM(total)', 'readonly' => true],
            ];
        }
    }

    public function loadOrCreateReport()
    {
        $this->report = \App\Models\DailyReport::firstOrNew([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'tanggal' => now()->toDateString(),
        ]);

        if ($this->report->exists && isset($this->report->data['rincian'])) {
            $this->rincian = $this->report->data['rincian'];
            $this->rekap = $this->report->data['rekap'];
        } else {
            // Bagian ini hanya berjalan saat membuat laporan BARU
            $this->rincian = [];
            for ($i = 0; $i < 10; $i++) {
                $this->tambahBarisRincian(false);
            }

            // --- PERBAIKAN BUG NILAI DEFAULT ---
            foreach($this->configRekap as $field) {
                // Prioritaskan nilai default jika ada dan tidak kosong
                if (isset($field['default_value']) && $field['default_value'] !== '') {
                    $this->rekap[$field['name']] = $field['default_value'];
                } else {
                    // Jika tidak ada default, gunakan logika lama
                    $this->rekap[$field['name']] = ($field['type'] == 'date') ? now()->format('Y-m-d') : '';
                }
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

    public function selectRow($index)
    {
        $this->selectedRowIndex = $this->selectedRowIndex === $index ? null : $index;
    }

    public function hapusBarisTerpilih()
    {
        if ($this->selectedRowIndex !== null && isset($this->rincian[$this->selectedRowIndex])) {
            unset($this->rincian[$this->selectedRowIndex]);
            $this->rincian = array_values($this->rincian);
            $this->selectedRowIndex = null;
            $this->hitungUlang();
        }
    }

    // PERUBAHAN: Method ini sekarang hanya dipanggil saat input kehilangan fokus (blur)
    public function updated($name, $value)
    {
        $this->hitungUlang();
    }

    public function hitungUlang()
    {
        foreach ($this->configRekap as $field) {
            if (!empty($field['formula'])) {
                $formula = $field['formula'];

                // --- Tahap 1: FUNGSI BARU PAIRPALC(kolom1 * kolom2) ---
                // Mencari semua fungsi PAIRPALC
                preg_match_all('/PAIRPALC\(([^ "]+)\s*([*+\/-])\s*([^")]+)\)/', $formula, $pairpMatches, PREG_SET_ORDER);

                foreach ($pairpMatches as $match) {
                    $col1 = trim($match[1]);
                    $operator = trim($match[2]);
                    $col2 = trim($match[3]);
                    $totalPairResult = 0;

                    // Lakukan perhitungan per baris di tabel rincian
                    foreach ($this->rincian as $row) {
                        $val1 = $this->parseNumber($row[$col1] ?? 0);
                        $val2 = $this->parseNumber($row[$col2] ?? 0);
                        $pairResult = 0;
                        switch ($operator) {
                            case '*': $pairResult = $val1 * $val2; break;
                            case '+': $pairResult = $val1 + $val2; break;
                            case '-': $pairResult = $val1 - $val2; break;
                            case '/': $pairResult = $val2 != 0 ? $val1 / $val2 : 0; break;
                        }
                        $totalPairResult += $pairResult;
                    }
                    // Ganti fungsi PAIRPALC di formula dengan hasilnya
                    $formula = str_replace($match[0], $totalPairResult, $formula);
                }


                // --- Tahap 2: Fungsi Agregat (SUM, SUBT) ---
                // Kalkulator SUM()
                preg_match_all('/SUM\((.*?)\)/', $formula, $sumMatches);
                foreach ($sumMatches[1] as $colToSum) {
                    $sum = collect($this->rincian)->sum(fn($item) => $this->parseNumber($item[trim($colToSum)] ?? 0));
                    $formula = str_replace("SUM(" . trim($colToSum) . ")", $sum, $formula);
                }

                // Kalkulator SUBT()
                preg_match_all('/SUBT\(([^,]+),\s*([^)]+)\)/', $formula, $subtMatches, PREG_SET_ORDER);
                foreach ($subtMatches as $match) {
                    $initialValueExpr = trim($match[1]);
                    $colToSubtract = trim($match[2]);
                    $initialValue = is_numeric($initialValueExpr) ? (float)$initialValueExpr : $this->parseNumber($this->rekap[$initialValueExpr] ?? 0);
                    $sumOfSubtractColumn = collect($this->rincian)->sum(fn($item) => $this->parseNumber($item[$colToSubtract] ?? 0));
                    $result = $initialValue - $sumOfSubtractColumn;
                    $formula = str_replace($match[0], $result, $formula);
                }

                foreach ($this->rekap as $key => $value) {
                    if (is_string($key)) {
                        // PERUBAHAN: Bandingkan dalam huruf kecil semua (case-insensitive)
                        if (strpos(strtolower($formula), strtolower($key)) !== false) {
                            $numericValue = $this->parseNumber($value);
                            // Ganti nama kolom di formula (apapun kapitalisasinya) dengan nilainya
                            $formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/i', (string)$numericValue, $formula);
                        }
                    }
                }

                // --- Tahap 4: Evaluasi Final ---
                // Hitung hasil akhir dari formula yang sudah diproses
                $this->rekap[$field['name']] = $this->evaluateFormula($formula);
            }
        }
    }


    private function evaluateFormula($formula)
    {
        try {
            // Sanitasi sederhana untuk keamanan, hanya izinkan karakter matematika dasar
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

    // OPTIMASI: Menambahkan feedback loading pada proses simpan
    public function simpanLaporan()
    {
        $dataToStore = [
            'rekap' => $this->rekap,
            'rincian' => array_values(array_filter($this->rincian, fn($row) => collect($row)->filter()->isNotEmpty()))
        ];

        \Illuminate\Support\Facades\DB::transaction(function () use ($dataToStore) {
            $this->report->data = $dataToStore;
            $this->report->save();
            \App\Models\Barang::where('daily_report_id', $this->report->id)->delete();
            foreach ($dataToStore['rincian'] as $item) {
                \App\Models\Barang::create([
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
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
