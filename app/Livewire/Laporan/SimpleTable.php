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
                $this->date  = $report->tanggal;
                $this->title = $report->data['meta']['title'] ?? '';
                // Gunakan data tersimpan jika ada
                if (!empty($report->data)) {
                    $this->columns = $report->data['columns'] ?? [];
                    $this->rows    = $report->data['rows']    ?? [];
                }
            }
        }
        // Inisialisasi kolom default jika kosong
        if (empty($this->columns)) {
            $this->columns = range('A', 'E');
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
            // Sanitasi: hanya izinkan tag sederhana untuk styling
            $cleanValue = strip_tags($value, '<b><i><u><s><span>');
            $this->rows[$rowIndex][$column] = $cleanValue;
            $this->dispatch('tableUpdated');
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
            'date' => 'required|date',
        ]);
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
                ],
            ]);
        }
        $this->reportId = $report->id;
        session()->flash('success', 'Laporan berhasil disimpan.');
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
            'date' => 'required|date',
        ]);
        $this->save();
        return redirect()->route('client.laporan.preview', $this->reportId);
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