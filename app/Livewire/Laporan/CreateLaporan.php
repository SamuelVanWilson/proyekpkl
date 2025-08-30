<?php

namespace App\Livewire\Laporan;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DailyReport;
use App\Models\Barang;
// PERBAIKAN UTAMA DI SINI:
// Mengubah alamat 'use' statement agar menunjuk ke folder Models
use App\Models\TableConfiguration;

class CreateLaporan extends Component
{
    // Properti untuk menampung konfigurasi form dari database
    public $formConfig = [];

    // Properti untuk menyimpan data dari form dinamis
    public $rekapData = [];

    // Properti yang tetap (tidak dinamis)
    public $rincian = [];

    // Properti untuk hasil kalkulasi real-time
    public $jumlah_karung = 0;
    public $total_bruto = 0;
    public $total_netto = 0;
    public $harga_bruto = 0;
    public $total_uang = 0;

    // Aturan validasi
    protected function rules()
    {
        // Membuat aturan validasi dinamis berdasarkan form config
        $rekapRules = collect($this->formConfig)->mapWithKeys(function ($field) {
            return ['rekapData.' . $field['name'] => 'required'];
        })->toArray();

        return array_merge($rekapRules, [
            'rincian' => 'required|array|min:1',
            'rincian.*.total' => 'required|numeric|min:0',
        ]);
    }

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
                ['name' => 'karung_kosong', 'label' => 'Karung Kosong (Kg)', 'type' => 'number'],
                ['name' => 'harga_per_kilo', 'label' => 'Harga per Kilo', 'type' => 'number'],
                ['name' => 'uang_muka', 'label' => 'Uang Muka', 'type' => 'number'],
            ];
        }

        // Inisialisasi rekapData agar tidak error dan set tanggal default
        foreach($this->formConfig as $field) {
            if ($field['name'] === 'tanggal') {
                $this->rekapData[$field['name']] = now()->format('Y-m-d');
            } else {
                 $this->rekapData[$field['name']] = '';
            }
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
    public function updated($propertyName)
    {
        $this->hitungUlang();
    }

    // Logika utama untuk kalkulasi
    public function hitungUlang()
    {
        // Mengambil nilai dari form dinamis dengan fallback 0
        $karung_kosong = is_numeric($this->rekapData['karung_kosong'] ?? 0) ? $this->rekapData['karung_kosong'] : 0;
        $harga_per_kilo = is_numeric($this->rekapData['harga_per_kilo'] ?? 0) ? $this->rekapData['harga_per_kilo'] : 0;
        $uang_muka = is_numeric($this->rekapData['uang_muka'] ?? 0) ? $this->rekapData['uang_muka'] : 0;

        $this->jumlah_karung = count($this->rincian);
        $this->total_bruto = collect($this->rincian)->sum(function($item){
            return is_numeric($item['total']) ? $item['total'] : 0;
        });

        $this->total_netto = $this->total_bruto - $karung_kosong;
        $this->harga_bruto = $this->total_netto * $harga_per_kilo;
        $this->total_uang = $this->harga_bruto - $uang_muka;
    }

    // Method untuk menyimpan data ke database
    public function simpanLaporan()
    {
        $this->validate();

        DB::transaction(function () {
            // Simpan semua data dari form dinamis dan kalkulasi ke satu kolom JSON
            $dataToStore = array_merge($this->rekapData, [
                'jumlah_karung' => $this->jumlah_karung,
                'total_bruto' => $this->total_bruto,
                'total_netto' => $this->total_netto,
                'harga_bruto' => $this->harga_bruto,
                'total_uang' => $this->total_uang,
            ]);

            $dailyReport = DailyReport::create([
                'user_id' => Auth::id(),
                'tanggal' => $this->rekapData['tanggal'], // Simpan tanggal di kolom terpisah untuk sorting
                'data' => $dataToStore,
            ]);

            // Simpan rincian ke tabel barangs
            foreach ($this->rincian as $item) {
                if(!empty($item['total'])) {
                    Barang::create([
                        'user_id' => Auth::id(),
                        'daily_report_id' => $dailyReport->id,
                        'data' => $item,
                    ]);
                }
            }
        });

        session()->flash('success', 'Laporan berhasil disimpan.');
        // Setelah menyimpan, alihkan ke daftar histori laporan
        return redirect()->route('client.laporan.histori');
    }

    public function harian()
    {
        return view('client.laporan.harian');
    }
}
