<div>
    {{-- Header Halaman dengan Tombol Simpan & Export --}}
    <div class="flex flex-wrap gap-4 justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Laporan Hari Ini</h1>
            <p class="text-base text-gray-600">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('client.laporan.histori.pdf', $report->id) }}" target="_blank" 
               class="rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
               Export PDF
            </a>
            <button wire:click="simpanLaporan" 
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                Simpan
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mt-4 bg-green-100 text-green-800 text-sm font-medium p-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-8 space-y-6">
        {{-- ==================================================================== --}}
        {{--            Tabel Rincian dengan Gaya Spreadsheet BARU              --}}
        {{-- ==================================================================== --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Tabel Rincian</h2>
            </div>
            
            {{-- Container Spreadsheet dengan scroll horizontal --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    {{-- Header Kolom Dinamis --}}
                    <thead class="bg-gray-50">
                        <tr class="divide-x divide-gray-200">
                            <th class="sticky left-0 bg-gray-50 px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-12 z-10">#</th>
                            @foreach($configRincian as $col)
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $col['label'] }}</th>
                            @endforeach
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($rincian as $index => $row)
                            <tr class="divide-x divide-gray-200">
                                {{-- Nomor Baris Sticky --}}
                                <td class="sticky left-0 bg-white px-3 py-1 text-center text-gray-500 w-12">{{ $index + 1 }}</td>
                                
                                {{-- Input Dinamis Sesuai Konfigurasi --}}
                                @foreach($configRincian as $col)
                                    <td class="px-1 py-1 whitespace-nowrap">
                                        <input 
                                            type="{{ $col['type'] }}" 
                                            wire:model.live.debounce.500ms="rincian.{{ $index }}.{{ $col['name'] }}" 
                                            class="w-full border-none focus:ring-2 focus:ring-blue-500 rounded-md text-sm"
                                            style="min-width: 120px;">
                                    </td>
                                @endforeach

                                {{-- Tombol Hapus --}}
                                <td class="px-3 py-1 text-center">
                                    <button wire:click="hapusBarisRincian({{ $index }})" class="text-gray-400 hover:text-red-500 p-2 rounded-full transition-colors">
                                        <ion-icon name="trash-outline" class="text-lg"></ion-icon>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-gray-200">
                <button wire:click="tambahBarisRincian" class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Baris</button>
            </div>
        </div>

        {{-- Formulir Rekapitulasi Dinamis (Tidak Berubah) --}}
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Formulir Rekapitulasi</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($configRekap as $field)
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ $field['label'] }}</label>
                        @if(empty($field['formula']))
                            <input type="{{ $field['type'] }}" wire:model.live.debounce.500ms="rekap.{{ $field['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
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
