<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DailyReport;

/**
 * Komponen Livewire untuk Laporan Biasa.
 *
 * Komponen ini menampilkan tabel sederhana untuk memasukkan data barang
 * seperti nama, kategori, jumlah, berat, dan harga. Pengguna dapat
 * menambah baris baru sesuai kebutuhan. Data akan disimpan ke kolom
 * `data` pada tabel daily_reports sebagai array `rows`. Tidak ada
 * rekapitulasi atau perhitungan otomatis di sini karena fitur tersebut
 * dikhususkan untuk laporan lanjutan berbayar.
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
     * Barisan data barang. Setiap elemen berisi kunci:
     * - nama_barang
     * - kategori
     * - jumlah
     * - berat_barang
     * - harga
     *
     * @var array
     */
    public $rows = [];

    /**
     * Jalankan sekali ketika komponen di‑mount.
     */
    public function mount()
    {
        // Tanggal default adalah hari ini dalam format Y-m-d
        $this->date = now()->format('Y-m-d');
        // Inisialisasi dengan satu baris kosong
        $this->rows = [
            ['nama_barang' => '', 'kategori' => '', 'jumlah' => '', 'berat_barang' => '', 'harga' => ''],
        ];
    }

    /**
     * Tambah baris kosong baru.
     */
    public function addRow()
    {
        $this->rows[] = ['nama_barang' => '', 'kategori' => '', 'jumlah' => '', 'berat_barang' => '', 'harga' => ''];
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
     * Simpan laporan ke database sebagai DailyReport.
     */
    public function save()
    {
        $this->validate([
            'date' => 'required|date',
            'rows' => 'required|array|min:1',
            'rows.*.nama_barang' => 'required|string|max:255',
            'rows.*.kategori' => 'required|string|max:255',
            'rows.*.jumlah' => 'required|numeric|min:0',
            'rows.*.berat_barang' => 'required|numeric|min:0',
            'rows.*.harga' => 'required|numeric|min:0',
        ]);

        // Buat atau perbarui DailyReport untuk tanggal yang sama
        DailyReport::updateOrCreate([
            'user_id' => Auth::id(),
            'tanggal' => $this->date,
        ], [
            'data' => ['rows' => $this->rows],
        ]);

        session()->flash('success', 'Laporan berhasil disimpan.');
        // Redirect ke histori agar pengguna melihat daftar laporan
        return redirect()->route('client.laporan.histori');
    }

    public function render()
    {
        return view('livewire.laporan.simple-table');
    }
}