<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;
use App\Models\TableConfiguration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Barang;

/**
 * Komponen Livewire untuk halaman Laporan Advanced.
 *
 * Komponen ini memuat konfigurasi kolom rincian dan rekap dari table configuration,
 * menyediakan fitur tambah/hapus baris, menghitung ulang rumus rekap, menyimpan laporan,
 * serta memuat data laporan dari penyimpanan lokal (localStorage) saat offline.
 */
class Harian extends Component
{
    /**
     * Model laporan yang sedang dikerjakan.
     *
     * @var \App\Models\DailyReport
     */
    public $report;

    /**
     * Data rincian sebagai array baris. Setiap baris adalah array kolom.
     *
     * @var array<int, array<string, mixed>>
     */
    public $rincian = [];

    /**
     * Data rekap sebagai key => value.
     *
     * @var array<string, mixed>
     */
    public $rekap = [];

    /**
     * Konfigurasi kolom rincian dari TableConfiguration.
     *
     * @var array<int, array<string, mixed>>
     */
    public $configRincian = [];

    /**
     * Konfigurasi kolom rekap dari TableConfiguration.
     *
     * @var array<int, array<string, mixed>>
     */
    public $configRekap = [];

    /**
     * Indeks baris yang dipilih saat ini (untuk penghapusan).
     *
     * @var int|null
     */
    public $selectedRowIndex = null;

    /**
     * Listener Livewire untuk memuat data dari localStorage.
     *
     * @var array<string, string>
     */
    protected $listeners = ['loadDataFromLocalStorage' => 'loadFromLocalStorage'];

    /**
     * Memuat data dari localStorage ketika event dipicu dari Alpine.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    public function loadFromLocalStorage($data)
    {
        // Hanya isi data jika $rincian di server masih kosong (laporan baru)
        // dan jika data dari local storage tidak kosong
        $isServerDataEmpty = collect($this->rincian)->flatten()->filter()->isEmpty();
        if ($isServerDataEmpty && !empty($data)) {
            $this->rincian = $data;
            $this->hitungUlang();
        }
    }

    /**
     * Lifecycle hook Livewire: load konfigurasi dan laporan saat komponen di-mount.
     */
    public function mount()
    {
        $this->loadConfig();
        $this->loadOrCreateReport();
    }

    /**
     * Parsing string angka dengan pemisah ribuan/koma menjadi float.
     *
     * @param mixed $value
     * @return float
     */
    private function parseNumber($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.0;
        }
        // 1. Hapus semua karakter kecuali angka, koma, dan titik.
        $cleaned = preg_replace('/[^\d,.]/', '', (string) $value);
        // 2. Ganti koma desimal gaya Eropa dengan titik.
        $cleaned = str_replace(',', '.', $cleaned);
        // 3. Hapus titik pemisah ribuan kecuali tiga karakter terakhir (desimal)
        $cleaned = str_replace('.', '', substr($cleaned, 0, -3)) . substr($cleaned, -3);

        return (float) $cleaned;
    }

    /**
     * Memuat konfigurasi kolom rincian dan rekap dari TableConfiguration.
     * Jika tidak ada konfigurasi user, gunakan konfigurasi default.
     */
    public function loadConfig()
    {
        $config = TableConfiguration::where('user_id', Auth::id())
                                    ->where('table_name', 'daily_reports')
                                    ->first();

        if ($config && !empty($config->columns)) {
            $this->configRincian = $config->columns['rincian'] ?? [];
            $this->configRekap   = $config->columns['rekap']   ?? [];
        } else {
            // Konfigurasi default jika tidak ada
            $this->configRincian = [
                ['name' => 'total', 'label' => 'Total', 'type' => 'number'],
            ];
            $this->configRekap = [
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date', 'formula' => null, 'readonly' => false],
                ['name' => 'lokasi',  'label' => 'Lokasi',  'type' => 'text', 'formula' => null, 'readonly' => false],
                ['name' => 'total_bruto', 'label' => 'Total Bruto', 'type' => 'number', 'formula' => 'SUM(total)', 'readonly' => true],
            ];
        }
    }

    /**
     * Memuat laporan existing atau membuat laporan baru untuk hari ini.
     * Jika sudah ada laporan hari ini, gunakan data yang ada.
     */
    public function loadOrCreateReport()
    {
        $this->report = DailyReport::firstOrNew([
            'user_id' => Auth::id(),
            'tanggal' => now()->toDateString(),
        ]);

        if ($this->report->exists && isset($this->report->data['rincian'])) {
            // Laporan lama: muat rincian dan rekap
            $this->rincian = $this->report->data['rincian'];
            $this->rekap   = $this->report->data['rekap'];
        } else {
            // Laporan baru: buat 10 baris kosong dan siapkan nilai rekap default
            $this->rincian = [];
            for ($i = 0; $i < 10; $i++) {
                $this->tambahBarisRincian(false);
            }

            // Set nilai rekap awal: gunakan default_value jika ada, jika tidak gunakan tanggal sekarang untuk tipe date
            foreach ($this->configRekap as $field) {
                if (isset($field['default_value']) && $field['default_value'] !== '') {
                    $this->rekap[$field['name']] = $field['default_value'];
                } else {
                    $this->rekap[$field['name']] = ($field['type'] === 'date') ? now()->format('Y-m-d') : '';
                }
            }
        }
        $this->hitungUlang();
    }

    /**
     * Menambah baris rincian baru.
     *
     * @param bool $recalculate
     * @return void
     */
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

    /**
     * Pilih atau batal pilih baris (untuk dihapus).
     *
     * @param int $index
     * @return void
     */
    public function selectRow($index)
    {
        $this->selectedRowIndex = $this->selectedRowIndex === $index ? null : $index;
    }

    /**
     * Hapus baris yang dipilih.
     *
     * @return void
     */
    public function hapusBarisTerpilih()
    {
        if ($this->selectedRowIndex !== null && isset($this->rincian[$this->selectedRowIndex])) {
            unset($this->rincian[$this->selectedRowIndex]);
            $this->rincian = array_values($this->rincian);
            $this->selectedRowIndex = null;
            $this->hitungUlang();
        }
    }

    /**
     * Trigger Livewire update setiap perubahan input.
     * Menghitung ulang rekap saat input berubah.
     */
    public function updated($name, $value)
    {
        $this->hitungUlang();
    }

    /**
     * Hitung ulang semua formula rekap berdasarkan data rincian dan rekap saat ini.
     * Mendukung fungsi custom PAIRPALC, SUM, SUBT, serta variabel rekap.
     */
    public function hitungUlang()
    {
        foreach ($this->configRekap as $field) {
            if (!empty($field['formula'])) {
                $formula = $field['formula'];

                // Tahap 1: FUNGSI BARU PAIRPALC(kolom1 * kolom2)
                preg_match_all('/PAIRPALC\(([^ "\)]+)\s*([*+\/-])\s*([^"\)]+)\)/', $formula, $pairpMatches, PREG_SET_ORDER);
                foreach ($pairpMatches as $match) {
                    $col1 = trim($match[1]);
                    $operator = trim($match[2]);
                    $col2 = trim($match[3]);
                    $totalPairResult = 0;
                    // Hitung per baris rincian
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

                // Tahap 2: Fungsi Agregat SUM()
                preg_match_all('/SUM\((.*?)\)/', $formula, $sumMatches);
                foreach ($sumMatches[1] as $colToSum) {
                    $sum = collect($this->rincian)->sum(fn($item) => $this->parseNumber($item[trim($colToSum)] ?? 0));
                    $formula = str_replace('SUM(' . trim($colToSum) . ')', $sum, $formula);
                }

                // Tahap 3: Fungsi SUBT(initial, col)
                preg_match_all('/SUBT\(([^,]+),\s*([^\)]+)\)/', $formula, $subtMatches, PREG_SET_ORDER);
                foreach ($subtMatches as $match) {
                    $initialValueExpr = trim($match[1]);
                    $colToSubtract    = trim($match[2]);
                    $initialValue = is_numeric($initialValueExpr)
                        ? (float) $initialValueExpr
                        : $this->parseNumber($this->rekap[$initialValueExpr] ?? 0);
                    $sumOfSubtractColumn = collect($this->rincian)->sum(fn($item) => $this->parseNumber($item[$colToSubtract] ?? 0));
                    $result = $initialValue - $sumOfSubtractColumn;
                    $formula = str_replace($match[0], $result, $formula);
                }

                // Tahap 4: Ganti variabel rekap ke nilainya (case-insensitive)
                foreach ($this->rekap as $key => $value) {
                    if (is_string($key) && strpos(strtolower($formula), strtolower($key)) !== false) {
                        $numericValue = $this->parseNumber($value);
                        // Ganti semua variasi huruf besar/kecil
                        $formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/i', (string) $numericValue, $formula);
                    }
                }

                // Tahap 5: Evaluasi formula sanitized
                $this->rekap[$field['name']] = $this->evaluateFormula($formula);
            }
        }
    }

    /**
     * Evaluasi ekspresi matematika sederhana secara aman.
     * Hanya karakter matematika dasar yang diperbolehkan.
     *
     * @param string $formula
     * @return float
     */
    private function evaluateFormula($formula)
    {
        try {
            $sanitizedFormula = preg_replace('/[^-0-9\.\+\*\/ \(\)]/', '', $formula);
            if (empty($sanitizedFormula) || !preg_match('/[0-9]/', $sanitizedFormula)) {
                return 0;
            }
            return @eval("return {$sanitizedFormula};") ?? 0;
        } catch (\Throwable $e) {
            Log::error('Formula evaluation error: ' . $e->getMessage() . ' | Original Formula: ' . $formula);
            return 0;
        }
    }

    /**
     * Simpan laporan ke database.
     * Perbarui tanggal laporan jika kolom rekap menyediakan tanggal.
     *
     * @return null
     */
    public function simpanLaporan()
    {
        $dataToStore = [
            'rekap'   => $this->rekap,
            'rincian' => array_values(array_filter($this->rincian, fn($row) => collect($row)->filter()->isNotEmpty())),
        ];

        DB::transaction(function () use ($dataToStore) {
            // Perbarui tanggal laporan berdasarkan kolom rekap 'tanggal' jika tersedia
            if (isset($this->rekap['tanggal']) && !empty($this->rekap['tanggal'])) {
                try {
                    $this->report->tanggal = \Carbon\Carbon::parse($this->rekap['tanggal'])->toDateString();
                } catch (\Exception $e) {
                    // Jika parsing gagal, simpan nilai apa adanya
                    $this->report->tanggal = $this->rekap['tanggal'];
                }
            }
            // Simpan struktur data laporan
            $this->report->data = $dataToStore;
            $this->report->save();

            // Segarkan entri Barang (rincian) untuk laporan ini
            Barang::where('daily_report_id', $this->report->id)->delete();
            foreach ($dataToStore['rincian'] as $item) {
                Barang::create([
                    'user_id'        => Auth::id(),
                    'daily_report_id'=> $this->report->id,
                    'data'           => $item,
                ]);
            }
        });

        // Beritahu frontend bahwa laporan telah disimpan
        $this->dispatch('laporanDisimpan');
        session()->flash('success', 'Laporan hari ini berhasil disimpan/diperbarui!');
        // Tidak ada redirect karena kita tetap di halaman advanced
        return null;
    }

    /**
     * Render komponen.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.laporan.harian');
    }
}