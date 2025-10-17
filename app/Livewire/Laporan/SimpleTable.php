<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;

/**
 * Komponen Livewire untuk laporan sederhana (biasa).
 *
 * Menyediakan tabel dinamis mirip spreadsheet dengan kolom A-Z dan baris tak terbatas.
 * Pengguna dapat menambah/menghapus baris dan kolom, mengedit sel dengan format dasar,
 * menyimpan laporan ke database, dan melihat preview PDF.
 */
class SimpleTable extends Component
{
    /**
     * ID laporan yang sedang diedit. Null saat membuat laporan baru.
     *
     * @var int|null
     */
    public $reportId = null;

    /**
     * Tanggal laporan.
     *
     * @var string
     */
    public $date;

    /**
     * Array daftar kolom (huruf A-Z).
     *
     * @var array<int, string>
     */
    public $columns = [];

    /**
     * Array daftar baris. Setiap baris adalah array keyed by kolom.
     *
     * @var array<int, array<string, mixed>>
     */
    public $rows = [];

    /**
     * Judul laporan. Disimpan dalam meta.
     *
     * @var string
     */
    public $title = '';

    /**
     * Index kolom yang dipilih untuk dihapus. Digunakan oleh UI.
     *
     * @var int|null
     */
    public $selectedColumnIndex = null;

    /**
     * Skema detail untuk laporan sederhana. Setiap item berisi key, label dan tipe (text/number/date).
     * Disimpan dalam report->data['detail_schema'].
     *
     * @var array<int,array<string,mixed>>
     */
    public $detailSchema = [];

    /**
     * Nilai untuk setiap field dalam detailSchema. Key mengacu pada field key.
     * Disimpan dalam report->data['detail_values'].
     *
     * @var array<string,mixed>
     */
    public $detailValues = [];

    /**
     * Baris yang dipilih untuk dihapus.
     *
     * @var int|null
     */
    public $selectedRowIndex = null;

    /**
     * Menyimpan keadaan tabel sebelum penghapusan baris/kolom untuk fitur undo.
     *
     * @var array|null
     */
    public $lastRowsState = null;

    /**
     * Menyimpan keadaan kolom sebelum penghapusan kolom untuk fitur undo.
     *
     * @var array|null
     */
    public $lastColumnsState = null;

    /**
     * Menandai apakah aksi penghapusan dapat di-undo.
     *
     * @var bool
     */
    public $undoAvailable = false;

    /**
     * Inisialisasi komponen. Jika reportId diberikan, muat data laporan.
     *
     * @param int|null $reportId
     */
    public function mount($reportId = null)
    {
        $this->reportId = $reportId;
        // Jika ID laporan diberikan, muat laporan dari database
        if ($this->reportId) {
            $report = DailyReport::where('id', $this->reportId)
                ->where('user_id', Auth::id())
                ->first();
            if ($report) {
                $this->date  = optional($report->tanggal)->toDateString();
                $this->title = $report->data['meta']['title'] ?? '';
                // Muat skema detail dan nilai dari laporan jika tersedia
                if (!empty($report->data['detail_schema'])) {
                    $this->detailSchema = $report->data['detail_schema'];
                }
                if (!empty($report->data['detail_values'])) {
                    $this->detailValues = $report->data['detail_values'];
                }
                // Jika laporan memiliki konfigurasi simple table tersimpan (simple_config)
                if (!empty($report->data['simple_config']['columns'])) {
                    $this->columns = $report->data['simple_config']['columns'];
                }
                // Gunakan data tersimpan jika ada
                if (!empty($report->data)) {
                    $this->rows = $report->data['rows'] ?? [];
                }
                // Sinkronisasi judul & tanggal dari detail values jika tersedia
                if (isset($this->detailValues['title'])) {
                    $this->title = $this->detailValues['title'];
                }
                if (isset($this->detailValues['tanggal_raw'])) {
                    $this->date = $this->detailValues['tanggal_raw'];
                }
                // Pastikan setiap field dalam detailSchema memiliki nilai default jika belum ada
                if (!empty($this->detailSchema)) {
                    foreach ($this->detailSchema as $field) {
                        $key = $field['key'];
                        if (!array_key_exists($key, $this->detailValues)) {
                            if ($key === 'title') {
                                $this->detailValues[$key] = $this->title;
                            } elseif ($key === 'tanggal_raw') {
                                $this->detailValues[$key] = $this->date;
                            } else {
                                $this->detailValues[$key] = '';
                            }
                        }
                    }
                }
            }
        }
        // Inisialisasi kolom default jika kosong, gunakan 5 kolom awal A-E
        if (empty($this->columns)) {
            $this->columns = ['A', 'B', 'C', 'D', 'E'];
        }
        // Inisialisasi baris default jika kosong
        if (empty($this->rows)) {
            for ($i = 0; $i < 10; $i++) {
                $row = [];
                foreach ($this->columns as $col) {
                    $row[$col] = '';
                }
                $this->rows[] = $row;
            }
        }
        // Set tanggal default untuk laporan baru
        if (empty($this->date)) {
            $this->date = now()->format('Y-m-d');
        }

        // Inisialisasi detail schema untuk laporan baru jika kosong
        if (empty($this->detailSchema)) {
            $this->detailSchema = [
                ['key' => 'title',       'label' => 'Judul Laporan',   'type' => 'text'],
                ['key' => 'tanggal_raw', 'label' => 'Tanggal Laporan', 'type' => 'date'],
            ];
        }
        // Inisialisasi detail values default
        if (empty($this->detailValues)) {
            foreach ($this->detailSchema as $field) {
                if ($field['key'] === 'title') {
                    $this->detailValues[$field['key']] = $this->title;
                } elseif ($field['key'] === 'tanggal_raw') {
                    $this->detailValues[$field['key']] = $this->date;
                } else {
                    $this->detailValues[$field['key']] = '';
                }
            }
        }
    }

    /**
     * Tambah baris baru di tabel.
     *
     * @return void
     */
    public function addRow()
    {
        $row = [];
        foreach ($this->columns as $col) {
            $row[$col] = '';
        }
        $this->rows[] = $row;
        $this->dispatch('tableUpdated');
    }

    public function removeLastRow()
    {
        if (!empty($this->rows)) {
            // Simpan keadaan sebelumnya untuk undo
            $this->lastRowsState    = $this->rows;
            $this->lastColumnsState = $this->columns;
            $this->undoAvailable    = true;
            array_pop($this->rows);
            $this->dispatch('tableUpdated');
        }
    }

    /**
     * Hapus baris berdasarkan index.
     *
     * @param int $index
     * @return void
     */
    public function removeRow($index)
    {
        // Simpan keadaan sebelumnya untuk undo
        $this->lastRowsState    = $this->rows;
        $this->lastColumnsState = $this->columns;
        $this->undoAvailable    = true;
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->dispatch('tableUpdated');
    }

    /**
     * Tambah kolom baru sampai Z.
     *
     * @return void
     */
    public function addColumn()
    {
        $last = end($this->columns);
        if ($last === 'Z') {
            return;
        }
        $next = chr(ord($last) + 1);
        $this->columns[] = $next;
        foreach ($this->rows as $i => $row) {
            $this->rows[$i][$next] = '';
        }
        $this->dispatch('tableUpdated');
    }

    /**
     * Hapus kolom terakhir.
     *
     * @return void
     */
    public function removeLastColumn()
    {
        $last = end($this->columns);
        if ($last) {
            array_pop($this->columns);
            foreach ($this->rows as $i => $row) {
                unset($this->rows[$i][$last]);
            }
            $this->dispatch('tableUpdated');
        }
    }

    /**
     * Perbarui nilai sel.
     *
     * @param int $rowIndex
     * @param string $column
     * @param string $value
     * @return void
     */
    public function updateCell($rowIndex, $column, $value)
    {
        if (isset($this->rows[$rowIndex]) && in_array($column, $this->columns)) {
            // Bersihkan HTML dan konversi <font> tag menjadi span dengan style agar jenis font dan ukuran tersimpan
            $cleanValue = $this->normalizeHtml($value);
            $this->rows[$rowIndex][$column] = $cleanValue;
            $this->dispatch('tableUpdated');
        }
    }

    /**
     * Normalisasi HTML untuk sel tabel.
     * Menghapus tag <script> dan <a>, mengonversi tag <font> menjadi <span style="...">,
     * serta mengizinkan tag inline aman. Ini memastikan format seperti jenis font dan ukuran
     * font yang dipilih pengguna tetap tersimpan saat laporan dimuat ulang.
     *
     * @param string $html
     * @return string
     */
    private function normalizeHtml(string $html): string
    {
        // Buang <script> dan <a> agar tidak tersimpan link
        $html = preg_replace('#<script.*?</script>#is', '', $html);
        $html = preg_replace('#<a[^>]*>(.*?)</a>#is', '$1', $html);
        // Konversi tag <font> menjadi <span style="...">
        $html = preg_replace_callback('#<font([^>]*)>#i', function ($m) {
            $attrs = $m[1];
            $face = null;
            $size = null;
            if (preg_match('/face="?([^"\']+)"?/i', $attrs, $f)) {
                $face = $f[1] ?? null;
            }
            if (preg_match('/size="?([1-7])"?/i', $attrs, $s)) {
                $size = $s[1] ?? null;
            }
            $map = [1 => '8pt', 2 => '10pt', 3 => '12pt', 4 => '14pt', 5 => '18pt', 6 => '24pt', 7 => '36pt'];
            $style = '';
            if ($face) {
                $style .= 'font-family:' . $face . ';';
            }
            if ($size) {
                $style .= 'font-size:' . ($map[$size] ?? '12pt') . ';';
            }
            return '<span style="' . $style . '">';
        }, $html);
        $html = str_ireplace('</font>', '</span>', $html);

        // Gunakan DOMDocument untuk mempertahankan atribut style aman dan tag inline tertentu
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8"?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $allowedTags = ['b','strong','i','em','u','s','span','div','p','br'];
        $allowedStyles = ['font-family','font-size','text-align','font-weight','font-style','text-decoration'];
        $this->sanitizeDom($doc->documentElement, $allowedTags, $allowedStyles);
        $innerHtml = '';
        foreach ($doc->documentElement->childNodes as $child) {
            $innerHtml .= $doc->saveHTML($child);
        }
        libxml_clear_errors();
        return $innerHtml;
    }

    /**
     * Rekursif sanitasi DOM: menghapus tag tak diizinkan dan membersihkan atribut.
     * (Duplikat dari Harian.php untuk menjaga kemandirian.)
     *
     * @param \DOMNode $node
     * @param array<int,string> $allowedTags
     * @param array<int,string> $allowedStyles
     * @return void
     */
    private function sanitizeDom(\DOMNode $node, array $allowedTags, array $allowedStyles): void
    {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $el = $node;
            $tagName = strtolower($el->nodeName);
            if (!in_array($tagName, $allowedTags)) {
                $fragment = $el->ownerDocument->createDocumentFragment();
                while ($el->firstChild) {
                    $fragment->appendChild($el->firstChild);
                }
                $el->parentNode->replaceChild($fragment, $el);
                return;
            }
            // Bersihkan atribut style
            if ($el->hasAttribute('style')) {
                $style = $el->getAttribute('style');
                $newStyles = [];
                foreach (explode(';', $style) as $part) {
                    $part = trim($part);
                    if ($part === '' || strpos($part, ':') === false) continue;
                    list($prop, $val) = array_map('trim', explode(':', $part, 2));
                    $propLower = strtolower($prop);
                    if (in_array($propLower, $allowedStyles)) {
                        $newStyles[] = $propLower . ':' . $val;
                    }
                }
                if (!empty($newStyles)) {
                    $el->setAttribute('style', implode('; ', $newStyles));
                } else {
                    $el->removeAttribute('style');
                }
            }
            // Hapus atribut selain style
            if ($el->hasAttributes()) {
                $remove = [];
                foreach (iterator_to_array($el->attributes) as $attr) {
                    if (strtolower($attr->nodeName) !== 'style') {
                        $remove[] = $attr->nodeName;
                    }
                }
                foreach ($remove as $attrName) {
                    $el->removeAttribute($attrName);
                }
            }
        }
        for ($child = $node->firstChild; $child; $child = $child->nextSibling) {
            $this->sanitizeDom($child, $allowedTags, $allowedStyles);
        }
    }

    /**
     * Simpan laporan ke database.
     * Memperbarui existing report jika reportId ada, atau membuat baru jika tidak.
     *
     * @return null
     */
    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'date'  => 'required|date',
        ]);
        // sinkronisasi judul & tanggal ke detailValues jika ada key-nya
        if (isset($this->detailValues['title'])) {
            $this->detailValues['title'] = $this->title;
        }
        if (isset($this->detailValues['tanggal_raw'])) {
            $this->detailValues['tanggal_raw'] = $this->date;
        }
        $user = Auth::user();
        // Batasi jumlah laporan sederhana untuk pengguna non‑premium (maks 2).
        // Hitung hanya laporan "biasa" (tanpa rincian & rekap) sebagai batasan, bukan total semua laporan.
        if (!$user->hasActiveSubscription()) {
            // Ambil semua laporan pengguna lalu filter hanya laporan yang bukan advanced (tidak memiliki key rincian & rekap).
            $simpleReportCount = DailyReport::where('user_id', $user->id)
                ->get()
                ->filter(function ($rep) {
                    $data = $rep->data ?? [];
                    return !(isset($data['rincian']) && isset($data['rekap']));
                })
                ->count();
            // Jika membuat laporan baru (tidak sedang edit) dan jumlah laporan sederhana sudah >= 2, tolak pembuatan.
            if (!$this->reportId && $simpleReportCount >= 2) {
                session()->flash('error', 'Pengguna tanpa langganan hanya dapat memiliki 2 laporan biasa. Silakan hapus laporan lama atau berlangganan untuk menambah laporan.');
                return null;
            }
        }
        // Update existing report
        if ($this->reportId) {
            $report = DailyReport::where('id', $this->reportId)
                ->where('user_id', Auth::id())
                ->first();
            if ($report) {
                $report->tanggal = \Carbon\Carbon::parse($this->date)->toDateString();
                $report->data = [
                    'columns' => $this->columns,
                    'rows'    => $this->rows,
                    'meta'    => [
                        'title' => $this->title,
                    ],
                    'detail_schema' => $this->detailSchema,
                    'detail_values' => $this->detailValues,
                    // Simpan konfigurasi kolom khusus untuk laporan ini
                    'simple_config' => [
                        'columns' => $this->columns,
                    ],
                ];
                $report->save();
            } else {
                // Jika tidak ditemukan, buat baru
                $report = DailyReport::create([
                    'user_id' => Auth::id(),
                    'tanggal' => \Carbon\Carbon::parse($this->date)->toDateString(),
                    'data' => [
                        'columns' => $this->columns,
                        'rows'    => $this->rows,
                        'meta'    => [
                            'title' => $this->title,
                        ],
                        'detail_schema' => $this->detailSchema,
                        'detail_values' => $this->detailValues,
                    ],
                ]);
            }
        } else {
            // Create new report
            $report = DailyReport::create([
                'user_id' => Auth::id(),
                'tanggal' => \Carbon\Carbon::parse($this->date)->toDateString(),
                'data' => [
                    'columns' => $this->columns,
                    'rows'    => $this->rows,
                    'meta'    => [
                        'title' => $this->title,
                    ],
                    'detail_schema' => $this->detailSchema,
                    'detail_values' => $this->detailValues,
                    // Simpan konfigurasi kolom untuk laporan baru
                    'simple_config' => [
                        'columns' => $this->columns,
                    ],
                ],
            ]);
        }
        $this->reportId = $report->id;
        session()->flash('success', 'Laporan berhasil disimpan.');
        // pastikan property date tetap ISO agar input date tampil benar
        $this->date = \Carbon\Carbon::parse($this->date)->toDateString();
        $this->dispatch('reportSaved');
        return null;
    }

    /**
     * Simpan laporan lalu arahkan ke halaman preview.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function preview()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'date'  => 'required|date',
        ]);
        $this->save();
        return redirect()->route('client.laporan.preview', $this->reportId);
    }

    public function selectRow($index)
    {
        // Toggle: jika klik baris yang sama -> batalkan pilihan
        if ($this->selectedRowIndex === $index) {
            $this->selectedRowIndex = null;
        } else {
            $this->selectedRowIndex = $index;
        }
    }

    /**
     * Pilih kolom untuk dihapus. Menetapkan selectedColumnIndex.
     *
     * @param int $index
     * @return void
     */
    public function selectColumn($index)
    {
        // Toggle: jika klik kolom yang sama -> batalkan pilihan
        if ($this->selectedColumnIndex === $index) {
            $this->selectedColumnIndex = null;
        } else {
            $this->selectedColumnIndex = $index;
        }
    }

    /**
     * Hapus baris yang sedang dipilih.
     *
     * @return void
     */
    public function deleteSelectedRow()
    {
        if ($this->selectedRowIndex !== null && isset($this->rows[$this->selectedRowIndex])) {
            $this->removeRow($this->selectedRowIndex);
            $this->selectedRowIndex = null;
        }
    }

    /**
     * Hapus kolom yang sedang dipilih.
     *
     * @return void
     */
    public function deleteSelectedColumn()
    {
        if ($this->selectedColumnIndex !== null && isset($this->columns[$this->selectedColumnIndex])) {
            // Simpan keadaan sebelumnya untuk undo
            $this->lastRowsState    = $this->rows;
            $this->lastColumnsState = $this->columns;
            $this->undoAvailable    = true;
            // Kosongkan isi kolom yang dipilih (label tetap)
            $colKey = $this->columns[$this->selectedColumnIndex];
            foreach ($this->rows as $r => $row) {
                $this->rows[$r][$colKey] = '';
            }
            // Hapus kolom terakhir agar jumlah kolom berkurang satu
            $lastKey = array_pop($this->columns);
            if ($lastKey !== null) {
                foreach ($this->rows as $r => $row) {
                    unset($this->rows[$r][$lastKey]);
                }
            }
            // Reset pilihan kolom
            $this->selectedColumnIndex = null;
            $this->dispatch('tableUpdated');
        }
    }

    /**
     * Kembalikan keadaan tabel sebelum penghapusan baris/kolom.
     * Memulihkan baris dan kolom dari state cadangan dan menonaktifkan fitur undo.
     *
     * @return void
     */
    public function undoDelete()
    {
        if ($this->undoAvailable && is_array($this->lastRowsState)) {
            $this->rows = $this->lastRowsState;
            if (is_array($this->lastColumnsState)) {
                $this->columns = $this->lastColumnsState;
            }
            $this->undoAvailable    = false;
            $this->lastRowsState    = null;
            $this->lastColumnsState = null;
            $this->selectedRowIndex    = null;
            $this->selectedColumnIndex = null;
            $this->dispatch('tableUpdated');
        }
    }

    /**
     * Tambah field baru ke skema detail. Field bertipe text secara default.
     *
     * @return void
     */
    public function addDetailField()
    {
        $key = 'field_' . substr(md5(microtime()), 0, 6);
        $this->detailSchema[] = [
            'key'   => $key,
            'label' => 'Field Baru',
            'type'  => 'text',
        ];
        $this->detailValues[$key] = '';
    }

    /**
     * Hapus field detail berdasarkan index.
     *
     * @param int $index
     * @return void
     */
    public function removeDetailField($index)
    {
        if (isset($this->detailSchema[$index])) {
            $key = $this->detailSchema[$index]['key'];
            unset($this->detailSchema[$index]);
            $this->detailSchema = array_values($this->detailSchema);
            unset($this->detailValues[$key]);
        }
    }

    /**
     * Render komponen.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.laporan.simple-table');
    }
}