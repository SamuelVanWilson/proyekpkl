<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DailyReport;
use App\Models\Barang;

class CreateLaporan extends Component
{
    // Properti untuk menampung konfigurasi form dari database
    public $formConfig = [];

    // Properti untuk menyimpan data dari form dinamis
    public $rekapData = [];

    // Properti yang tetap (tidak dinamis)
    public $rincian = [];

    // Method ini dijalankan saat komponen pertama kali dimuat
    public function mount()
    {
        // Ambil 'blueprint' untuk user ini dari database
        $config = TableConfiguration::where('user_id', Auth::id())
                                    ->where('table_name', 'daily_reports')
                                    ->first();

        // Jika ada blueprint, gunakan. Jika tidak, pakai blueprint default.
        if ($config && !empty($config->columns)) {
            $this->formConfig = $config->columns;
        } else {
            // Blueprint default jika belum diatur admin
            $this->formConfig = [
                ['name' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date'],
                ['name' => 'lokasi', 'label' => 'Lokasi', 'type' => 'text'],
                ['name' => 'pemilik_sawah', 'label' => 'Pemilik Sawah', 'type' => 'text'],
            ];
        }

        // Inisialisasi rekapData agar tidak error
        foreach($this->formConfig as $field) {
            $this->rekapData[$field['name']] = '';
        }

        $this->tambahBarisRincian();
    }

    // Aksi untuk menambah baris baru di tabel rincian
    public function tambahBarisRincian()
    {
        $this->rincian[] = ['total' => ''];
    }

    // Aksi untuk menghapus baris dari tabel rincian
    public function hapusBarisRincian($index)
    {
        unset($this->rincian[$index]);
        $this->rincian = array_values($this->rincian); // Re-index array
        $this->hitungUlang(); // Hitung ulang setelah menghapus
    }

    // Method "ajaib" dari Livewire.
    // Setiap kali ada properti yang di-update di frontend (misal: user mengetik),
    // method ini akan dipanggil secara otomatis.
    public function updated($propertyName)
    {
        $this->hitungUlang();
    }

    // Method untuk menyimpan data ke database
    public function simpanLaporan()
    {
        // Validasi sekarang menjadi lebih dinamis
        $this->validate([
            'rekapData.*' => 'required', // Aturan simpel, bisa dibuat lebih detail
            'rincian.*.total' => 'required|numeric',
        ]);

        // Simpan semua data dari form dinamis ke satu kolom JSON
        DailyReport::create([
            'user_id' => Auth::id(),
            'data' => $this->rekapData, // <-- KUNCI UTAMA ADA DI SINI
            'rincian_data' => $this->rincian, // Simpan rincian juga di JSON
            // ... simpan hasil kalkulasi jika perlu ...
        ]);

        session()->flash('success', 'Laporan berhasil disimpan.');
        return redirect()->route('client.report.index');
    }

    public function render()
    {
        return view('livewire.laporan.create-laporan');
    }
}
