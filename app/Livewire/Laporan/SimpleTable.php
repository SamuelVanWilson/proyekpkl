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

        // Jika mengedit laporan yang sudah ada
        if ($this->reportId) {
            $report = DailyReport::where('id', $this->reportId)
                ->where('user_id', Auth::id())
                ->first();
            if (!$report) {
                abort(403);
            }
        } else {
            // Cari laporan berdasarkan user dan tanggal, atau buat baru
            $report = DailyReport::firstOrNew([
                'user_id' => Auth::id(),
                'tanggal' => $this->date,
            ]);
        }

        // Set nilai dan simpan
        $report->user_id = Auth::id();
        $report->tanggal = $this->date;
        $report->data = [
            'columns' => $this->columns,
            'rows'    => $this->rows,
        ];
        $report->save();
        // Perbarui reportId (berguna saat edit)
        $this->reportId = $report->id;

        session()->flash('success', 'Laporan berhasil disimpan.');
        // Redirect ke histori agar pengguna melihat daftar laporan
        return redirect()->route('client.laporan.histori');
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