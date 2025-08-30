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
     */
    public function mount()
    {
        // Tanggal default adalah hari ini dalam format Y-m-d
        $this->date = now()->format('Y-m-d');
        // Inisialisasi kolom default: A sampai E
        $this->columns = range('A', 'E');

        // Inisialisasi 10 baris awal dengan kolom kosong
        for ($i = 0; $i < 10; $i++) {
            $row = [];
            foreach ($this->columns as $col) {
                $row[$col] = '';
            }
            $this->rows[] = $row;
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

        // Simpan ke tabel daily_reports: data berisi kolom dan baris
        DailyReport::updateOrCreate([
            'user_id' => Auth::id(),
            'tanggal' => $this->date,
        ], [
            'data' => [
                'columns' => $this->columns,
                'rows'    => $this->rows,
            ],
        ]);

        session()->flash('success', 'Laporan berhasil disimpan.');
        // Redirect ke histori agar pengguna melihat daftar laporan
        return redirect()->route('client.laporan.histori');
    }

    /**
     * Unduh data sebagai file CSV.
     */
    public function export()
    {
        $fileName = 'laporan-' . $this->date . '.csv';
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            // Header: kolom pertama kosong untuk nomor baris
            $header = array_merge([''], $this->columns);
            fputcsv($handle, $header);
            foreach ($this->rows as $index => $row) {
                $rowData = [ $index + 1 ];
                foreach ($this->columns as $col) {
                    $value = $row[$col] ?? '';
                    // Strip tags untuk CSV (hilangkan HTML formatting)
                    $rowData[] = trim(strip_tags($value));
                }
                fputcsv($handle, $rowData);
            }
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        return view('livewire.laporan.simple-table');
    }
}