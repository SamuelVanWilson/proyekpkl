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
                // Jika laporan memiliki konfigurasi simple table tersimpan (simple_config)
                if (!empty($report->data['simple_config']['columns'])) {
                    $this->columns = $report->data['simple_config']['columns'];
                }
                // Gunakan data tersimpan jika ada
                if (!empty($report->data)) {
                    $this->rows    = $report->data['rows']    ?? [];
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

    /**
     * Hapus baris terakhir.
     *
     * @return void
     */
    public function removeLastRow()
    {
        if (!empty($this->rows)) {
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
        $html = preg_replace('#<a[^>]*>(.*?)</a>#i', '$1', $html);
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
        // Izinkan tag inline aman
        return strip_tags($html, '<b><strong><i><em><u><s><span><div><p><br>');
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
        // Batasi jumlah laporan untuk pengguna non‑premium (max 2)
        if (!$user->hasActiveSubscription()) {
            $countReports = DailyReport::where('user_id', $user->id)->count();
            if (!$this->reportId && $countReports >= 2) {
                session()->flash('error', 'Pengguna tanpa langganan hanya dapat memiliki 2 laporan. Silakan hapus laporan lama atau berlangganan untuk menambah laporan.');
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

    /**
     * Pilih baris untuk dihapus. Menetapkan selectedRowIndex.
     *
     * @param int $index
     * @return void
     */
    public function selectRow($index)
    {
        $this->selectedRowIndex = $index;
    }

    /**
     * Pilih kolom untuk dihapus. Menetapkan selectedColumnIndex.
     *
     * @param int $index
     * @return void
     */
    public function selectColumn($index)
    {
        $this->selectedColumnIndex = $index;
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
            // Hanya kosongkan isi kolom, jangan hapus label kolom
            $colKey = $this->columns[$this->selectedColumnIndex];
            foreach ($this->rows as $r => $row) {
                $this->rows[$r][$colKey] = '';
            }
            // Reset pilihan kolom
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