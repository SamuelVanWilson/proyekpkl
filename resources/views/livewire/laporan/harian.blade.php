<div>
    {{-- CSS Khusus untuk Spreadsheet dengan Tinggi Tetap --}}
    <style>
        .spreadsheet-container {
            height: 45vh;
            overflow: auto;
            position: relative;
            background-color: white;
            border: 1px solid #e5e7eb;
        }
        .spreadsheet {
            border-collapse: collapse;
            position: relative;
            min-width: 100%;
        }
        .spreadsheet th,
        .spreadsheet td {
            border-width: 0 0 1px 1px;
            border-color: #e5e7eb;
            position: relative;
            padding: 0;
            height: 38px;
            background-color: white;
        }
        .spreadsheet tr th:first-child, .spreadsheet tr td:first-child { border-left-width: 0; }
        .spreadsheet thead tr:first-child th { border-top-width: 0; }

        .spreadsheet thead th {
            background-color: #f9fafb;
            font-weight: 500;
            font-size: 0.75rem;
            color: #6b7280;
            text-align: center;
            user-select: none;
            position: sticky;
            top: 0;
            z-index: 20;
        }
        .spreadsheet tbody th {
            background-color: #f9fafb;
            font-weight: 500;
            font-size: 0.75rem;
            color: #6b7280;
            text-align: center;
            user-select: none;
            position: sticky;
            left: 0;
            width: 50px;
            min-width: 50px;
            z-index: 10;
            cursor: pointer; /* Menandakan bisa diklik */
        }
        .spreadsheet thead th:first-child {
            left: 0;
            z-index: 30;
        }
        .cell-input {
            width: 100%; height: 100%; border: none;
            padding: 5px 8px; font-size: 0.875rem;
            outline: none; background-color: transparent;
        }
        .cell-input:focus { box-shadow: inset 0 0 0 2px #3b82f6; }
        .resize-handle {
            position: absolute; right: -4px; top: 0; bottom: 0;
            width: 8px; cursor: col-resize; z-index: 40;
        }
        /* PERUBAHAN: Style untuk baris yang dipilih */
        .spreadsheet tbody tr.bg-blue-100,
        .spreadsheet tbody tr.bg-blue-100 th {
            background-color: #dbeafe; /* Warna biru muda */
        }
    </style>

    {{-- Header Halaman --}}
    <div class="mt-8 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            {{-- PEROMBAKAN TOOLBAR SPREADSHEET --}}
            <div class="p-3 border-b border-gray-200 flex items-center space-x-2">
                <h2 class="text-lg font-semibold text-gray-800 mr-auto">Tabel Rincian</h2>

                {{-- Tombol Hapus Baris (hanya muncul jika ada baris dipilih) --}}
                @if($selectedRowIndex !== null)
                <button wire:click="hapusBarisTerpilih" class="flex items-center text-sm font-medium text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg">
                    <ion-icon name="trash-outline" class="mr-1 text-lg"></ion-icon> Hapus Baris
                </button>
                @endif

                {{-- Tombol Tambah Baris --}}
                <button wire:click="tambahBarisRincian" class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg">
                    <ion-icon name="add-circle-outline" class="mr-1 text-lg"></ion-icon> Tambah Baris
                </button>

                {{-- TOMBOL KONFIGURASI BARU --}}
                <a href="{{ route('client.laporan.form-builder') }}" class="flex items-center text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 p-2 rounded-lg">
                    <ion-icon name="settings-outline" class="text-lg"></ion-icon>
                </a>
            </div>

            <div class="spreadsheet-container">
                <table class="spreadsheet">
                    <thead>
                        <tr>
                            <th class="sticky left-0 bg-gray-50 z-10 w-12">#</th>
                            @foreach($configRincian as $col)
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $col['label'] ?? $col['name'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rincian as $index => $row)
                            {{-- PERUBAHAN: Menambahkan class jika baris dipilih --}}
                            <tr wire:key="rincian-{{ $index }}" class="{{ $selectedRowIndex === $index ? 'bg-blue-100' : '' }}">
                                {{-- PERUBAHAN: Jadikan nomor baris sebagai tombol untuk memilih --}}
                                <th wire:click="selectRow({{ $index }})" class="sticky left-0 z-10 w-12 hover:bg-gray-200 transition-colors">
                                    {{ $index + 1 }}
                                </th>
                                @foreach($configRincian as $col)
                                    <td>
                                        <input
                                            type="{{ $col['type'] }}"
                                            wire:model.live.debounce.300ms="rincian.{{ $index }}.{{ $col['name'] }}"
                                            class="cell-input"
                                            style="min-width: 150px;">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Formulir Rekapitulasi (Tidak ada perubahan di sini) --}}
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Formulir Rekapitulasi</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($configRekap as $field)
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ $field['label'] ?? $field['name'] }}</label>
                        @if(empty($field['formula']))
                            <input type="{{ $field['type'] }}" wire:model.live.debounce.300ms="rekap.{{ $field['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @else
                            <div class="mt-1 block w-full rounded-md bg-gray-100 px-3 py-2 text-gray-700">
                                {{ ($field['type'] == 'number') ? number_format((float) ($rekap[$field['name']] ?? 0), 0, ',', '.') : ($rekap[$field['name']] ?? '-') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
