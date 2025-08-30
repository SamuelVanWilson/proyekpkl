<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;

/**
 * Komponen Livewire untuk Laporan Biasa.
 *
 * Komponen ini menampilkan grid mirip Excel dengan kolom berlabel A, B, C
 * dan seterusnya sampai Z serta baris bernomor 1 sampai tak terbatas.
 * Pengguna dapat menambah baris maupun kolom secara dinamis. Setiap sel
 * bersifat editable (contenteditable) sehingga Anda dapat mengetik data
 * bebas, bahkan menerapkan format teks seperti bold/italic dengan toolbar.
 * Saat menyimpan, data disimpan sebagai struktur JSON dalam kolom
 * `data` pada tabel daily_reports berupa array `columns` dan `rows`.
 * Fitur rekapitulasi tetap khusus untuk laporan lanjutan berbayar sehingga
 * tidak diimplementasikan di sini.
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
     * Tanggal laporan. Digunakan sebagai kunci unik untuk setiap hari.
     *
     * @var string
     */
    public $date;

    /**
     * Daftar kolom saat ini. Berupa huruf A, B, C, dst.
     *
     * @var array<int,string>
     */
    public $columns = [];

    /**
     * Data baris. Setiap baris adalah array yang diindeks oleh huruf kolom.
     *
     * @var array<int, array<string,mixed>>
     */
    public $rows = [];

    /**
     * Judul laporan. Disimpan di meta data.
     *
     * @var string
     */
    public $title = '';

    /**
     * Baris yang dipilih untuk dihapus (optional)
     *
     * @var int|null
     */
    public $selectedRowIndex = null;

    /**
     * Jalankan sekali ketika komponen di‑mount.
     * Jika diberikan reportId, muat data laporan untuk diedit.
     */
    public function mount($reportId = null)
    {
        $this->reportId = $reportId;

        // Jika ID laporan diberikan, coba muat dari database
        if ($this->reportId) {
            $report = DailyReport::where('id', $this->reportId)
                ->where('user_id', Auth::id())
                ->first();

            if ($report) {
                // Gunakan tanggal dari laporan
                $this->date = $report->tanggal;
                // Muat meta data jika ada
                $this->title = $report->data['meta']['title'] ?? '';
                // Jika data tersedia (laporan biasa), gunakan kolom dan baris tersimpan
                if (!empty($report->data)) {
                    $this->columns = $report->data['columns'] ?? [];
                    $this->rows = $report->data['rows'] ?? [];
                }
            }
        }

        // Jika belum ada kolom (laporan baru), inisialisasi default A-E
        if (empty($this->columns)) {
            $this->columns = range('A', 'E');
        }

        // Jika belum ada baris (laporan baru), inisialisasi 10 baris
        if (empty($this->rows)) {
            for ($i = 0; $i < 10; $i++) {
                $row = [];
                foreach ($this->columns as $col) {
                    $row[$col] = '';
                }
                $this->rows[] = $row;
            }
        }
        // Pastikan tanggal terisi jika belum (misalnya laporan baru)
        if (empty($this->date)) {
            $this->date = now()->format('Y-m-d');
        }
    }

    /**
     * Tambah baris kosong baru.
     */
    public function addRow()
    {
        // Tambah baris baru dengan kolom yang ada saat ini
        $row = [];
        foreach ($this->columns as $col) {
            $row[$col] = '';
        }
        $this->rows[] = $row;
    }

    /**
     * Hapus baris terakhir.
     */
    public function removeLastRow()
    {
        if (!empty($this->rows)) {
            array_pop($this->rows);
        }
    }

    /**
     * Hapus baris berdasarkan indeks.
     *
     * @param int $index
     */
    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
    }

    /**
     * Tambahkan kolom baru hingga maksimal Z.
     */
    public function addColumn()
    {
        // Ambil huruf terakhir, tentukan huruf berikutnya
        $last = end($this->columns);
        if ($last === 'Z') {
            return; // tidak bisa lebih dari Z
        }
        $next = chr(ord($last) + 1);
        $this->columns[] = $next;
        // Tambahkan sel kosong di setiap baris
        foreach ($this->rows as $i => $row) {
            $this->rows[$i][$next] = '';
        }
    }

    /**
     * Hapus kolom terakhir.
     */
    public function removeLastColumn()
    {
        $last = end($this->columns);
        if ($last) {
            array_pop($this->columns);
            // Hapus data untuk kolom ini dari setiap baris
            foreach ($this->rows as $i => $row) {
                unset($this->rows[$i][$last]);
            }
        }
    }

    /**
     * Perbarui nilai sel berdasarkan indeks baris dan nama kolom.
     *
     * @param int $rowIndex Indeks baris
     * @param string $column Nama kolom (huruf)
     * @param string $value Nilai baru (HTML dari cell)
     */
    public function updateCell($rowIndex, $column, $value)
    {
        // Pastikan indeks dan kolom valid
        if (isset($this->rows[$rowIndex]) && in_array($column, $this->columns)) {
            $this->rows[$rowIndex][$column] = $value;
        }
    }

    /**
     * Simpan laporan ke database sebagai DailyReport.
     */
    public function save()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        // Jika reportId sudah ada, update laporan tersebut
        if ($this->reportId) {
            $report = DailyReport::where('id', $this->reportId)
                ->where('user_id', Auth::id())
                ->first();
            if ($report) {
                $report->tanggal = $this->date;
                $report->data = [
                    'columns' => $this->columns,
                    'rows'    => $this->rows,
                    'meta'    => [
                        'title' => $this->title,
                    ],
                ];
                $report->save();
            } else {
                // Jika tidak ditemukan, buat baru
                $report = DailyReport::create([
                    'user_id' => Auth::id(),
                    'tanggal' => $this->date,
                    'data' => [
                        'columns' => $this->columns,
                        'rows'    => $this->rows,
                        'meta'    => [
                            'title' => $this->title,
                        ],
                    ],
                ]);
            }
        } else {
            // Membuat laporan baru tanpa mempertimbangkan tanggal
            $report = DailyReport::create([
                'user_id' => Auth::id(),
                'tanggal' => $this->date,
                'data' => [
                    'columns' => $this->columns,
                    'rows'    => $this->rows,
                    'meta'    => [
                        'title' => $this->title,
                    ],
                ],
            ]);
        }
        $this->reportId = $report->id;

        session()->flash('success', 'Laporan berhasil disimpan.');
        return null;
    }

    /**
     * Simpan laporan lalu alihkan ke halaman preview.
     */
    public function preview()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        // Pastikan data tersimpan terlebih dahulu
        $this->save();
        // Redirect ke halaman preview dengan ID laporan yang baru disimpan
        return redirect()->route('client.laporan.preview', $this->reportId);
    }
    
    /**
     * Fitur export CSV dihapus karena permintaan pengguna. PDF diekspor melalui controller.
     */
    // public function export() {}

    public function render()
    {
        return view('livewire.laporan.simple-table');
    }
}