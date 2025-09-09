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
            
            {{-- Toolbar format teks (copy dari halaman laporan biasa) --}}
            <div x-data="{ formatOpen: false, alignOpen: false }" class="flex flex-wrap items-center gap-2 mb-4">
                {{-- Format dropdown --}}
                <div class="relative inline-block text-left">
                    <button type="button" @click="formatOpen = !formatOpen" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm font-medium flex items-center gap-1">
                        <ion-icon name="create-outline" class="text-lg"></ion-icon>
                        <span>Format</span>
                        <ion-icon name="chevron-down-outline" class="text-xs"></ion-icon>
                    </button>
                    <div x-show="formatOpen" @click.away="formatOpen = false" class="absolute z-20 mt-1 w-40 bg-white border border-gray-200 rounded-md shadow-lg py-1">
                        <a href="#" onclick="document.execCommand('bold', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Tebal</a>
                        <a href="#" onclick="document.execCommand('italic', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Miring</a>
                        <a href="#" onclick="document.execCommand('underline', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Garis Bawah</a>
                        <a href="#" onclick="document.execCommand('strikeThrough', false, ''); formatOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Coret</a>
                        <div class="border-t border-gray-200 my-1"></div>
                        {{-- Font family selector inside dropdown --}}
                        <div class="px-3 py-2">
                            <label class="block text-xs mb-1">Font</label>
                            <select onchange="document.execCommand('fontName', false, this.value); formatOpen=false;" class="w-full bg-gray-100 border border-gray-200 rounded-md text-sm py-1 px-2">
                                <option value="Arial">Arial</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Courier New">Courier New</option>
                                <option value="Helvetica">Helvetica</option>
                            </select>
                        </div>
                        <div class="px-3 py-2">
                            <label class="block text-xs mb-1">Ukuran</label>
                            <select onchange="document.execCommand('fontSize', false, this.value); formatOpen=false;" class="w-full bg-gray-100 border border-gray-200 rounded-md text-sm py-1 px-2">
                                <option value="1">8pt</option>
                                <option value="2">10pt</option>
                                <option value="3" selected>12pt</option>
                                <option value="4">14pt</option>
                                <option value="5">18pt</option>
                                <option value="6">24pt</option>
                                <option value="7">36pt</option>
                            </select>
                        </div>
                    </div>
                </div>
                {{-- Align dropdown --}}
                <div class="relative inline-block text-left">
                    <button type="button" @click="alignOpen = !alignOpen" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-md text-sm font-medium flex items-center gap-1">
                        <ion-icon name="text-outline" class="text-lg"></ion-icon>
                        <span>Align</span>
                        <ion-icon name="chevron-down-outline" class="text-xs"></ion-icon>
                    </button>
                    <div x-show="alignOpen" @click.away="alignOpen = false" class="absolute z-20 mt-1 w-32 bg-white border border-gray-200 rounded-md shadow-lg py-1">
                        <a href="#" onclick="document.execCommand('justifyLeft', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Kiri</a>
                        <a href="#" onclick="document.execCommand('justifyCenter', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Tengah</a>
                        <a href="#" onclick="document.execCommand('justifyRight', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Kanan</a>
                        <a href="#" onclick="document.execCommand('justifyFull', false, ''); alignOpen=false; return false;" class="block px-3 py-2 text-sm hover:bg-gray-100">Justify</a>
                    </div>
                </div>
            </div>


            {{-- KARTU TABEL RINCIAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-full">

                {{-- Toolbar Aksi --}}
                <div class="p-3 border-b border-gray-200 flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold text-gray-800 mr-auto">Tabel Rincian</h2>
                    <a href="{{ route('client.laporan.form-builder') }}" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
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
                                            {{-- Gunakan contenteditable agar toolbar format teks berfungsi. --}}
                                            <div
                                                contenteditable="true"
                                                class="w-full h-full px-2 py-1 text-sm outline-none min-w-[150px]"
                                                wire:input.debounce.500ms="updateCell({{ $index }}, '{{ $col['name'] }}', $event.target.innerHTML)"
                                                wire:key="cell-{{ $index }}-{{ $col['name'] }}"
                                            >{!! $row[$col['name']] ?? '' !!}</div>
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

            {{-- KARTU DETAIL LAPORAN --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Detail Laporan</h2>
                {{-- Judul dan tanggal laporan (editable) --}}
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Judul Laporan</label>
                        <input type="text" wire:model.lazy="reportTitle" class="input-modern" placeholder="Masukkan judul laporan">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Tanggal Laporan</label>
                        <input type="date" wire:model.lazy="rekap.tanggal" class="input-modern">
                    </div>
                    {{-- Kolom rekap lainnya --}}
                    @foreach($configRekap as $field)
                        <div>
                            <label class="text-sm font-medium text-gray-600 capitalize">{{ $field['label'] ?? $field['name'] }}</label>
                            @if(!empty($field['formula']) || !empty($field['readonly']))
                                {{-- Blok ini untuk nilai yang tidak bisa diedit --}}
                                <div class="mt-1 block w-full rounded-md bg-gray-100 px-3 py-2 text-gray-700 text-sm">
                                    @php
                                        $value = $rekap[$field['name']] ?? 0;
                                        $type = $field['type'] ?? 'text';
                                        $formattedValue = $value;
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
                                {{-- Nilai yang bisa diedit --}}
                                <input type="{{ $field['type'] }}" wire:model.blur="rekap.{{ $field['name'] }}" class="input-modern">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol aksi (Tambah/Hapus baris, Konfigurasi, Simpan, Preview) --}}
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                {{-- Grup baris: plus dan minus baris --}}
                <div class="flex justify-between items-center rounded-lg overflow-hidden bg-gray-200 text-gray-700 text-sm font-medium">
                    <button type="button" wire:click="tambahBarisRincian" class="flex-1 px-3 py-2 hover:bg-gray-300 flex items-center justify-center gap-1">
                        <span class="text-base font-bold">+</span><span>Baris</span>
                    </button>
                    <span class="h-full w-px bg-gray-300"></span>
                    <button type="button" wire:click="removeLastRow" @if(count($rincian) <= 1) disabled @endif class="flex-1 px-3 py-2 hover:bg-gray-300 flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="text-base font-bold">−</span><span>Baris</span>
                    </button>
                </div>
                {{-- Link konfigurasi tabel --}}
                <div class="flex justify-between items-center rounded-lg overflow-hidden bg-gray-200 text-gray-700 text-sm font-medium">
                    <a href="{{ route('client.laporan.form-builder') }}" class="flex-1 px-3 py-2 hover:bg-gray-300 flex items-center justify-center gap-1">
                        <span>Konfigurasi</span>
                    </a>
                </div>
                {{-- Simpan laporan --}}
                <button type="button" wire:click="simpanLaporan" wire:loading.attr="disabled" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full">
                    <span wire:loading.remove wire:target="simpanLaporan">Simpan</span>
                    <span wire:loading wire:target="simpanLaporan">Menyimpan...</span>
                </button>
                {{-- Preview laporan --}}
                <button type="button" wire:click="preview" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium w-full">
                    Preview
                </button>
            </div>

    </div>
</div>