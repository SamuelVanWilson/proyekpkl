<div>
    {{-- Header Halaman --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Laporan Hari Ini</h1>
            <p class="text-base text-gray-600">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <button wire:click="simpanLaporan" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
            Simpan
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mt-4 bg-green-100 text-green-800 text-sm font-medium p-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-8 space-y-6">
        {{-- Tabel Rincian Dinamis --}}
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Tabel Rincian</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            @foreach($configRincian as $col)
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ $col['label'] }}</th>
                            @endforeach
                            <th class="w-12"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($rincian as $index => $row)
                            <tr>
                                <td class="px-2 py-2 text-sm text-gray-500">{{ $index + 1 }}</td>
                                @foreach($configRincian as $col)
                                    <td class="px-2 py-1">
                                        <input type="{{ $col['type'] }}" wire:model.live="rincian.{{ $index }}.{{ $col['name'] }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    </td>
                                @endforeach
                                <td>
                                    <button wire:click="hapusBarisRincian({{ $index }})" class="text-gray-400 hover:text-red-500 p-2"><ion-icon name="trash-outline"></ion-icon></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button wire:click="tambahBarisRincian" class="mt-3 text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Baris</button>
        </div>

        {{-- Formulir Rekapitulasi Dinamis --}}
        <div class="bg-white p-4 rounded-xl shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800">Formulir Rekapitulasi</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($configRekap as $field)
                    <div>
                        <label class="text-sm font-medium text-gray-600">{{ $field['label'] }}</label>
                        @if(empty($field['formula']))
                            {{-- Input Manual --}}
                            <input type="{{ $field['type'] }}" wire:model.live="rekap.{{ $field['name'] }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @else
                            {{-- Tampilan Hasil Rumus (Read-only) --}}
                            <div class="mt-1 block w-full rounded-md bg-gray-100 px-3 py-2 text-gray-700">
                                {{ ($field['type'] == 'number') ? number_format($rekap[$field['name']] ?? 0, 0, ',', '.') : ($rekap[$field['name']] ?? '-') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
