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
     * Menyimpan keadaan rincian sebelum baris dihapus, untuk fitur undo.
     *
     * @var array|null
     */
    public $lastRincianState = null;

    /**
     * Menandai apakah fitur undo tersedia setelah penghapusan baris.
     *
     * @var bool
     */
    public $undoAvailable = false;
    /**
     * Model laporan yang sedang dikerjakan.
     *
     * @var \App\Models\DailyReport
     */
    public $report;

    /**
     * ID laporan yang sedang diedit (advanced). Null ketika membuat laporan baru.
     *
     * @var int|null
     */
    public $reportId = null;

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
     * Judul laporan untuk laporan advanced.
     * Disimpan dalam meta data report->data['meta'].
     *
     * @var string
     */
    public $reportTitle = '';

    /**
     * Menandakan apakah komponen menggunakan template default (dummy) karena belum ada konfigurasi.
     * Ketika true, tampilan akan menampilkan pesan agar pengguna melakukan konfigurasi terlebih dahulu.
     *
     * @var bool
     */
    public $isUsingDefaultTemplate = false;

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
    /**
     * Lifecycle hook Livewire: load konfigurasi dan laporan saat komponen di-mount.
     *
     * Jika $reportId diberikan maka muat laporan existing. Jika tidak, buat laporan baru.
     *
     * @param  int|null  $reportId
     * @return void
     */
    public function mount($reportId = null)
    {
        $this->loadConfig();
        // Simpan reportId untuk referensi selanjutnya
        $this->reportId = $reportId;
        // Jika ada reportId, coba muat laporan existing untuk user ini
        if ($this->reportId) {
            $existing = DailyReport::where('id', $this->reportId)
                ->where('user_id', Auth::id())
                ->first();
            if ($existing) {
                $this->report = $existing;
                // Ambil data rincian jika tersedia, gunakan konfigurasi saat ini
                $this->rincian = [];
                $savedRincian = $existing->data['rincian'] ?? [];
                // Pastikan setiap baris memiliki kolom sesuai konfigurasi (isi string kosong jika tidak ada)
                foreach ($savedRincian as $row) {
                    $newRow = [];
                    foreach ($this->configRincian as $col) {
                        $newRow[$col['name']] = $row[$col['name']] ?? '';
                    }
                    $this->rincian[] = $newRow;
                }
                // Jika tidak ada baris sama sekali, inisialisasi 10 baris kosong
                if (empty($this->rincian)) {
                    for ($i = 0; $i < 10; $i++) {
                        $newRow = [];
                        foreach ($this->configRincian as $col) {
                            $newRow[$col['name']] = '';
                        }
                        $this->rincian[] = $newRow;
                    }
                }
                // Ambil data rekap jika ada, atau default
                $this->rekap = [];
                $savedRekap = $existing->data['rekap'] ?? [];
                foreach ($this->configRekap as $field) {
                    if (array_key_exists($field['name'], $savedRekap)) {
                        $this->rekap[$field['name']] = $savedRekap[$field['name']];
                    } else {
                        if (isset($field['default_value']) && $field['default_value'] !== '') {
                            $this->rekap[$field['name']] = $field['default_value'];
                        } else {
                            $this->rekap[$field['name']] = ($field['type'] === 'date') ? now()->format('Y-m-d') : '';
                        }
                    }
                }
                // Ambil judul dari meta
                $meta = $existing->data['meta'] ?? [];
                $this->reportTitle = $meta['title'] ?? '';
                // Sinkronkan tanggal antara kolom rekap dan properti laporan:
                //  - Jika kolom rekap sudah memiliki nilai tanggal, pastikan properti $existing->tanggal
                //    diperbarui agar konsisten dengan input pengguna.
                //  - Jika kolom rekap belum memiliki nilai tanggal tetapi entitas laporan memiliki nilai
                //    tanggal, gunakan nilai tersebut sebagai default untuk kolom rekap. Hal ini mencegah
                //    field tanggal menjadi kosong ketika laporan dibuka kembali dari histori.
                if (isset($this->rekap['tanggal']) && !empty($this->rekap['tanggal'])) {
                    try {
                        $existing->tanggal = \Carbon\Carbon::parse($this->rekap['tanggal'])->toDateString();
                    } catch (\Exception $e) {
                        $existing->tanggal = $this->rekap['tanggal'];
                    }
                } else {
                    if (!empty($existing->tanggal)) {
                        try {
                            $this->rekap['tanggal'] = \Carbon\Carbon::parse($existing->tanggal)->toDateString();
                        } catch (\Exception $e) {
                            $this->rekap['tanggal'] = $existing->tanggal;
                        }
                    }
                }
                // Hitung ulang rumus rekap
                $this->hitungUlang();
                return;
            }
        }
        // Jika tidak ada laporan existing atau ID tidak ditemukan, buat laporan baru
        $this->report = new DailyReport([
            'user_id' => Auth::id(),
            'tanggal' => now()->toDateString(),
        ]);
        // Inisialisasi rincian dengan 10 baris kosong
        $this->rincian = [];
        for ($i = 0; $i < 10; $i++) {
            $newRow = [];
            foreach ($this->configRincian as $col) {
                $newRow[$col['name']] = '';
            }
            $this->rincian[] = $newRow;
        }
        // Inisialisasi rekap default
        $this->rekap = [];
        foreach ($this->configRekap as $field) {
            if (isset($field['default_value']) && $field['default_value'] !== '') {
                $this->rekap[$field['name']] = $field['default_value'];
            } else {
                $this->rekap[$field['name']] = ($field['type'] === 'date') ? now()->format('Y-m-d') : '';
            }
        }
        // Set judul default
        $this->reportTitle = '';
        $this->hitungUlang();
    }

    /**
     * Parsing string angka dengan pemisah ribuan/koma menjadi float.
     *
     * @param mixed $value
     * @return float
     */
    private function parseNumber($value): float
    {
        // Jika nilai kosong atau null, kembalikan nol
        if (is_null($value) || $value === '') {
            return 0.0;
        }
        // Hapus semua karakter selain digit, koma, titik dan minus
        $numStr = preg_replace('/[^0-9,\.\-]/', '', (string) $value);
        // Jika tidak ada digit, kembalikan nol
        if (!preg_match('/[0-9]/', $numStr)) {
            return 0.0;
        }
        // Temukan posisi pemisah desimal terakhir (baik titik maupun koma)
        $lastDot   = strrpos($numStr, '.');
        $lastComma = strrpos($numStr, ',');
        $lastPos   = false;
        if ($lastDot !== false && $lastComma !== false) {
            $lastPos = max($lastDot, $lastComma);
        } elseif ($lastDot !== false) {
            $lastPos = $lastDot;
        } elseif ($lastComma !== false) {
            $lastPos = $lastComma;
        }
        // Jika ada pemisah desimal
        if ($lastPos !== false) {
            $integerPart = preg_replace('/[.,]/', '', substr($numStr, 0, $lastPos));
            $decimalPart = preg_replace('/[.,]/', '', substr($numStr, $lastPos + 1));
            // Gabungkan integer dan desimal dengan titik sebagai pemisah
            $normalized = $integerPart . '.' . $decimalPart;
        } else {
            // Tidak ada desimal: hapus semua pemisah ribuan
            $normalized = preg_replace('/[.,]/', '', $numStr);
        }
        // Konversi ke float
        return (float) $normalized;
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
            // Ada konfigurasi tersimpan, tandai bahwa kita tidak lagi menggunakan template default
            $this->isUsingDefaultTemplate = false;
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
            // Tandai bahwa kita sedang menggunakan template default (dummy)
            $this->isUsingDefaultTemplate = true;
        }
    }

    /**
     * Memuat laporan existing atau membuat laporan baru untuk hari ini.
     * Jika sudah ada laporan hari ini, gunakan data yang ada.
     */
    /**
     * Fungsi loadOrCreateReport dihilangkan karena laporan advanced selalu baru.
     */
    public function loadOrCreateReport()
    {
        // Deprecated: tidak digunakan lagi.
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
     * Update nilai sel tertentu dan hitung ulang rekap.
     * Dipanggil dari input contenteditable pada view.
     *
     * @param int $rowIndex
     * @param string $column
     * @param string $value
     * @return void
     */
    public function updateCell($rowIndex, $column, $value)
    {
        // Pastikan baris valid. Kolom dapat saja belum ada (misal kolom baru), dalam hal ini kita buat key baru.
        if (!isset($this->rincian[$rowIndex])) {
            return;
        }

        // Cari konfigurasi kolom untuk menentukan tipe data
        $colConfig = null;
        foreach ($this->configRincian as $cfg) {
            if (isset($cfg['name']) && $cfg['name'] === $column) {
                $colConfig = $cfg;
                break;
            }
        }

        // Sanitasi berdasarkan tipe data
        if ($colConfig && isset($colConfig['type']) && $colConfig['type'] === 'number') {
            // Untuk kolom angka, hanya izinkan angka dan pemisah desimal. Hapus tag HTML.
            $plain = strip_tags($value);
            // Hapus semua karakter kecuali digit, koma, titik, minus
            $plain = preg_replace('/[^0-9,\.\-]/', '', (string) $plain);
            // Ubah koma desimal ke titik
            $plain = str_replace(',', '.', $plain);
            // Simpan string numerik apa adanya. Jika tidak ada angka valid, set kosong.
            $this->rincian[$rowIndex][$column] = $plain;
        } else {
            // Untuk tipe lain, gunakan normalisasi HTML untuk menjaga style inline
            $cleanValue = $this->normalizeHtml($value);
            $this->rincian[$rowIndex][$column] = $cleanValue;
        }
        // Hitung ulang nilai rekap setelah update
        $this->hitungUlang();
    }

    /**
     * Normalisasi HTML dari sel rincian. Menghapus tag berbahaya serta konversi
     * tag <font> menjadi <span style="font-family:...;font-size:..."> agar jenis
     * dan ukuran font tidak hilang saat disimpan. Izinkan hanya tag inline aman.
     *
     * @param string $html
     * @return string
     */
    private function normalizeHtml(string $html): string
    {
        // Hapus script tags untuk menghindari XSS
        $html = preg_replace('#<script.*?</script>#is', '', $html);
        // Hapus anchor tags, pertahankan teks di dalamnya
        $html = preg_replace('#<a[^>]*>(.*?)</a>#is', '$1', $html);
        // Konversi tag <font> ke <span> dengan style
        $html = preg_replace_callback('#<font([^>]*)>#i', function ($matches) {
            $attrs = $matches[1];
            $face = null;
            $size = null;
            if (preg_match('/face="?([^"\s]+)"?/i', $attrs, $faceMatch)) {
                $face = $faceMatch[1];
            }
            if (preg_match('/size="?([1-7])"?/i', $attrs, $sizeMatch)) {
                $size = $sizeMatch[1];
            }
            // Map ukuran execCommand ke pt
            $sizeMap = [1 => '8pt', 2 => '10pt', 3 => '12pt', 4 => '14pt', 5 => '18pt', 6 => '24pt', 7 => '36pt'];
            $style = '';
            if ($face) {
                $style .= 'font-family:' . $face . ';';
            }
            if ($size && isset($sizeMap[$size])) {
                $style .= 'font-size:' . $sizeMap[$size] . ';';
            }
            return '<span style="' . $style . '">';
        }, $html);
        // Tutup tag font jadi span
        $html = preg_replace('#</font>#i', '</span>', $html);

        /**
         * Sanitasi HTML dengan DOMDocument untuk mempertahankan atribut style aman.
         * Hanya izinkan tag inline tertentu dan properti style tertentu.
         */
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        // Bungkus HTML dengan div untuk memastikan fragment valid
        $doc->loadHTML('<?xml encoding="UTF-8"?>' . '<div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $allowedTags = ['b','strong','i','em','u','s','span','div','p','br'];
        $allowedStyles = ['font-family','font-size','text-align','font-weight','font-style','text-decoration'];
        $this->sanitizeDom($doc->documentElement, $allowedTags, $allowedStyles);
        // Ekstrak isi dalam wrapper div
        $innerHtml = '';
        foreach ($doc->documentElement->childNodes as $child) {
            $innerHtml .= $doc->saveHTML($child);
        }
        libxml_clear_errors();
        return $innerHtml;
    }

    /**
     * Rekursif sanitasi DOM: menghapus tag tak diizinkan dan membersihkan atribut.
     *
     * @param \DOMNode $node
     * @param array<int,string> $allowedTags
     * @param array<int,string> $allowedStyles
     * @return void
     */
    private function sanitizeDom(\DOMNode $node, array $allowedTags, array $allowedStyles): void
    {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            /** @var \DOMElement $el */
            $el = $node;
            $tagName = strtolower($el->nodeName);
            if (!in_array($tagName, $allowedTags)) {
                // Ganti node dengan children-nya (unwrap)
                $fragment = $el->ownerDocument->createDocumentFragment();
                while ($el->firstChild) {
                    $fragment->appendChild($el->firstChild);
                }
                $el->parentNode->replaceChild($fragment, $el);
                // Node baru sudah berada di posisi yang sama, lanjutkan tanpa turun lebih dalam
                return;
            }
            // Hanya pertahankan atribut style yang diperbolehkan
            if ($el->hasAttribute('style')) {
                $style = $el->getAttribute('style');
                $newStyles = [];
                foreach (explode(';', $style) as $part) {
                    $part = trim($part);
                    if ($part === '') continue;
                    if (strpos($part, ':') === false) continue;
                    [$prop, $val] = array_map('trim', explode(':', $part, 2));
                    $propLower = strtolower($prop);
                    if (in_array($propLower, $allowedStyles)) {
                        // Normalisasi properti nama
                        $newStyles[] = $propLower . ':' . $val;
                    }
                }
                if (!empty($newStyles)) {
                    $el->setAttribute('style', implode('; ', $newStyles));
                } else {
                    $el->removeAttribute('style');
                }
            }
            // Hapus atribut lain kecuali style
            if ($el->hasAttributes()) {
                $attrsToRemove = [];
                foreach (iterator_to_array($el->attributes) as $attr) {
                    if (strtolower($attr->nodeName) !== 'style') {
                        $attrsToRemove[] = $attr->nodeName;
                    }
                }
                foreach ($attrsToRemove as $attrName) {
                    $el->removeAttribute($attrName);
                }
            }
        }
        // Proses child nodes
        for ($child = $node->firstChild; $child; $child = $child->nextSibling) {
            $this->sanitizeDom($child, $allowedTags, $allowedStyles);
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
            $this->lastRincianState = $this->rincian; // untuk undo
            $this->undoAvailable = true;

            unset($this->rincian[$this->selectedRowIndex]);
            $this->rincian = array_values($this->rincian); // reindex biar wire:key sinkron

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
                // Normalisasi nama fungsi ke huruf besar agar regex peka huruf besar/kecil
                // Ini memungkinkan pengguna menulis "pairpalc", "sum", "subt", atau "counta" dengan huruf bebas.
                $formula = preg_replace('/\bpairpalc\b/i', 'PAIRPALC', $formula);
                $formula = preg_replace('/\bsum\b/i', 'SUM', $formula);
                $formula = preg_replace('/\bsubt\b/i', 'SUBT', $formula);
                $formula = preg_replace('/\bcounta\b/i', 'COUNTA', $formula);

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

                // Tahap 4: Fungsi COUNTA(col1, col2, ...)
                // Dukung satu atau lebih kolom dipisahkan koma. Hitung semua sel terisi dari kolom-kolom tersebut.
                preg_match_all('/COUNTA\(([^)]*)\)/', $formula, $countaMatches, PREG_SET_ORDER);
                foreach ($countaMatches as $match) {
                    $argsString = $match[1];
                    // Pisahkan argumen dengan koma
                    $cols = array_map('trim', explode(',', $argsString));
                    $count = 0;
                    foreach ($cols as $colSlug) {
                        if ($colSlug === '') continue;
                        foreach ($this->rincian as $row) {
                            $cellValue = $row[$colSlug] ?? '';
                            $text = trim(strip_tags((string) $cellValue));
                            if ($text !== '') {
                                $count++;
                            }
                        }
                    }
                    $formula = str_replace($match[0], (string) $count, $formula);
                }

                // Tahap 5: Ganti variabel rekap ke nilainya (case-insensitive)
                foreach ($this->rekap as $key => $value) {
                    if (is_string($key) && strpos(strtolower($formula), strtolower($key)) !== false) {
                        $numericValue = $this->parseNumber($value);
                        // Ganti semua variasi huruf besar/kecil
                        $formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/i', (string) $numericValue, $formula);
                    }
                }

                // Tahap 6: Evaluasi formula sanitized
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
        // Validasi judul laporan dan tanggal wajib diisi
        $this->validate([
            'reportTitle'    => 'required|string|max:255',
            'rekap.tanggal' => 'required|date',
        ], [
            'reportTitle.required'    => 'Judul Laporan tidak boleh kosong.',
            'rekap.tanggal.required'  => 'Tanggal Laporan tidak boleh kosong.',
        ]);
        // Persiapkan data yang akan disimpan. Selalu sertakan meta title.
        $cleanRincian = array_values(array_filter($this->rincian, fn($row) => collect($row)->filter()->isNotEmpty()));
        $meta = $this->report->data['meta'] ?? [];
        // Simpan judul laporan dari properti reportTitle
        $meta['title'] = $this->reportTitle;
        $dataToStore = [
            'meta'    => $meta,
            'rekap'   => $this->rekap,
            'rincian' => $cleanRincian,
        ];

        DB::transaction(function () use ($dataToStore) {
            // Perbarui tanggal laporan berdasarkan kolom rekap 'tanggal' jika tersedia
            if (isset($this->rekap['tanggal']) && !empty($this->rekap['tanggal'])) {
                try {
                    $this->report->tanggal = \Carbon\Carbon::parse($this->rekap['tanggal'])->toDateString();
                } catch (\Exception $e) {
                    $this->report->tanggal = $this->rekap['tanggal'];
                }
            }
            // Pastikan user_id selalu di-set ketika menyimpan laporan baru
            if (empty($this->report->user_id)) {
                $this->report->user_id = Auth::id();
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
        return null;
    }

    /**
     * Hapus baris terakhir dari rincian.
     * Digunakan untuk menyamakan UX dengan laporan biasa.
     *
     * @return void
     */
    public function removeLastRow()
    {
        if (!empty($this->rincian)) {
            // Simpan keadaan sebelum baris dihapus untuk undo
            $this->lastRincianState = $this->rincian;
            $this->undoAvailable   = true;
            array_pop($this->rincian);
            $this->hitungUlang();
        }
    }

    /**
     * Kembalikan baris yang dihapus sebelumnya jika undo tersedia.
     * Mengembalikan rincian ke keadaan sebelum penghapusan dan menonaktifkan undo.
     *
     * @return void
     */
    public function undoDelete()
    {
        if ($this->undoAvailable && is_array($this->lastRincianState)) {
            $this->rincian = $this->lastRincianState;
            $this->lastRincianState = null;
            $this->undoAvailable = false;
            $this->selectedRowIndex = null;
            // Hitung ulang rekap karena data mungkin berubah
            $this->hitungUlang();
        }
    }

    /**
     * Simpan laporan lalu tampilkan preview PDF.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function preview()
    {
        // Pastikan laporan tersimpan
        $this->simpanLaporan();
        // Redirect ke halaman preview PDF
        return redirect()->route('client.laporan.preview', $this->report->id);
    }

    /**
     * Buat laporan baru dengan mengosongkan data dan meta.
     * Mempertahankan konfigurasi tabel saat ini.
     *
     * @return void
     */
    public function newReport()
    {
        // Buat report baru tanpa id
        $this->report = new DailyReport([
            'user_id' => Auth::id(),
            'tanggal' => now()->toDateString(),
        ]);
        // Reset rincian menjadi jumlah baris default (10) dengan kolom config
        $this->rincian = [];
        for ($i = 0; $i < 10; $i++) {
            $newRow = [];
            foreach ($this->configRincian as $col) {
                $newRow[$col['name']] = '';
            }
            $this->rincian[] = $newRow;
        }
        // Reset rekap
        $this->rekap = [];
        foreach ($this->configRekap as $field) {
            if (isset($field['default_value']) && $field['default_value'] !== '') {
                $this->rekap[$field['name']] = $field['default_value'];
            } else {
                $this->rekap[$field['name']] = ($field['type'] === 'date') ? now()->format('Y-m-d') : '';
            }
        }
        // Reset judul laporan
        $this->reportTitle = '';
        // Kosongkan ID laporan karena membuat laporan baru
        $this->reportId = null;
        // Hapus local storage via event
        $this->dispatch('laporanDisimpan');
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