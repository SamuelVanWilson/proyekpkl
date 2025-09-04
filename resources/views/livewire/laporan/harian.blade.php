<div>
    {{-- (Style tidak berubah) --}}
    <style>
        .spreadsheet-container { height: 45vh; overflow: auto; position: relative; background-color: white; border: 1px solid #e5e7eb; }
        .spreadsheet { border-collapse: collapse; position: relative; min-width: 100%; }
        .spreadsheet th, .spreadsheet td { border-width: 0 0 1px 1px; border-color: #e5e7eb; position: relative; padding: 0; height: 38px; background-color: white; }
        .spreadsheet tr th:first-child, .spreadsheet tr td:first-child { border-left-width: 0; }
        .spreadsheet thead tr:first-child th { border-top-width: 0; }
        .spreadsheet thead th { background-color: #f9fafb; font-weight: 500; font-size: 0.75rem; color: #6b7280; text-align: center; user-select: none; position: sticky; top: 0; z-index: 20; }
        .spreadsheet tbody th { background-color: #f9fafb; font-weight: 500; font-size: 0.75rem; color: #6b7280; text-align: center; user-select: none; position: sticky; left: 0; width: 50px; min-width: 50px; z-index: 10; cursor: pointer; }
        .spreadsheet thead th:first-child { left: 0; z-index: 30; }
        .cell-input { width: 100%; height: 100%; border: none; padding: 5px 8px; font-size: 0.875rem; outline: none; background-color: transparent; }
        /* Ubah warna highlight dan fokus input ke hijau agar konsisten dengan tema Excel */
        .cell-input:focus { box-shadow: inset 0 0 0 2px #22c55e; }
        .spreadsheet tbody tr.bg-green-100, .spreadsheet tbody tr.bg-green-100 th { background-color: #dcfce7; }

    </style>

    {{-- OPTIMASI: Menambahkan indikator saat koneksi offline --}}
    <div x-data="{
        get storageKey() {
            // Membuat key unik untuk setiap laporan per hari
            return 'laporan_rincian_{{ $report->tanggal }}';
        },
        init() {
            // Saat komponen pertama kali dimuat
            console.log('Alpine init, loading from:', this.storageKey);
            const savedData = localStorage.getItem(this.storageKey);
            if (savedData) {
                // Jika ada data, kirim ke Livewire untuk dimuat
                $dispatch('loadDataFromLocalStorage', { data: JSON.parse(savedData) });
            }

            // Awasi perubahan pada data $rincian dari Livewire
            $watch('$wire.rincian', (newData) => {
                console.log('Data changed, saving to local storage...');
                localStorage.setItem(this.storageKey, JSON.stringify(newData));
            });

            // Dengar event dari server untuk membersihkan local storage
            window.addEventListener('laporanDisimpan', () => {
                console.log('Laporan disimpan, clearing local storage...');
                localStorage.removeItem(this.storageKey);
            });
        }
    }" class="flex flex-col gap-6 mb-[20em]" >


            {{-- Pesan Sukses --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            <div class="flex justify-end mb-2">
                <button wire:click="simpanLaporan" wire:loading.attr="disabled" class="w-full sm:w-auto px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:bg-green-300 flex items-center justify-center">
                    <span wire:loading.remove wire:target="simpanLaporan">Simpan Laporan</span>
                    <span wire:loading wire:target="simpanLaporan">Menyimpan...</span>
                </button>
            </div>

            {{-- KARTU TABEL RINCIAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-full">

                {{-- Toolbar Aksi --}}
                <div class="p-3 border-b border-gray-200 flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-800 mr-auto">Tabel Rincian</h2>
                    <button wire:click="tambahBarisRincian" class="px-3 py-1.5 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        Tambah Baris
                    </button>
                    <button wire:click="hapusBarisTerpilih" @if($selectedRowIndex === null) disabled @endif class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed">
                        Hapus Baris
                    </button>
                    <a href="{{ route('client.laporan.form-builder') }}" wire:navigate class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Konfigurasi
                    </a>
                </div>

                {{-- Kontainer Spreadsheet (Mengisi sisa ruang) --}}
                <div class="flex-grow overflow-auto">
                    <table class="spreadsheet min-w-full">
                        <thead class="sticky top-0 z-20 bg-gray-50">
                            <tr>
                                <th class="sticky left-0 z-30 bg-gray-100 w-12">#</th>
                                @foreach($configRincian as $col)
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $col['label'] ?? $col['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rincian as $index => $row)
                                <tr wire:key="rincian-{{ $index }}" class="{{ $selectedRowIndex === $index ? 'bg-green-100' : '' }}">
                                    <th wire:click="selectRow({{ $index }})" class="sticky left-0 z-10 w-12 bg-gray-50 hover:bg-gray-200 transition-colors cursor-pointer">
                                        {{ $index + 1 }}
                                    </th>
                                    @foreach($configRincian as $col)
                                        <td>
                                        <input
                                                type="{{ $col['type'] }}"
                                                wire:model.blur="rincian.{{ $index }}.{{ $col['name'] }}"
                                                class="w-full h-full border-none bg-transparent px-2 text-sm focus:ring-0 focus:bg-green-50" style="min-width: 150px;">
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($configRincian) + 1 }}" class="text-center text-gray-500 py-6">
                                        Tabel rincian kosong.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- KARTU FORMULIR REKAPITULASI --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Formulir Rekapitulasi</h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($configRekap as $field)
                        <div>
                            <label class="text-sm font-medium text-gray-600 capitalize">{{ $field['label'] ?? $field['name'] }}</label>
                            @if(!empty($field['formula']) || !empty($field['readonly']))
                                {{-- Blok ini untuk nilai yang tidak bisa diedit --}}
                                <div class="mt-1 block w-full rounded-md bg-gray-100 px-3 py-2 text-gray-700 text-sm">
                                    @php
                                        $value = $rekap[$field['name']] ?? 0;
                                        $type = $field['type'] ?? 'text';
                                        $formattedValue = $value; // Nilai default

                                        switch ($type) {
                                            case 'rupiah':
                                                $formattedValue = 'Rp ' . number_format((float)$value, 0, ',', '.');
                                                break;
                                            case 'dollar':
                                                $formattedValue = '$ ' . number_format((float)$value, 2, '.', ',');
                                                break;
                                            case 'kg':
                                                $formattedValue = number_format((float)$value, 2, '.', ',') . ' Kg';
                                                break;
                                            case 'g':
                                                $formattedValue = number_format((float)$value, 0, ',', '.') . ' g';
                                                break;
                                            case 'number':
                                                $formattedValue = number_format((float)$value, 0, ',', '.');
                                                break;
                                        }
                                    @endphp
                                    {{ $formattedValue }}
                                </div>
                            @else
                                {{-- Blok ini untuk nilai yang bisa diedit (tidak ada perubahan) --}}
                                <input type="{{ $field['type'] }}" wire:model.blur="rekap.{{ $field['name'] }}" class="input-modern">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
    </div>
</div>
