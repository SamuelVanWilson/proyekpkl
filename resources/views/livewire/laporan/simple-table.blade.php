<div>
    {{-- Pesan Sukses --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-800 text-sm font-medium p-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Input Tanggal --}}
    <div class="mb-4">
        <label for="date" class="block text-sm font-medium text-gray-700">Tanggal Laporan</label>
        <input type="date" id="date" wire:model="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        @error('date')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Tabel Data Barang --}}
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr class="text-gray-700 text-sm font-medium">
                    <th class="py-2 px-3 border-b">Nama Barang</th>
                    <th class="py-2 px-3 border-b">Kategori</th>
                    <th class="py-2 px-3 border-b">Jumlah</th>
                    <th class="py-2 px-3 border-b">Berat Barang (Kg)</th>
                    <th class="py-2 px-3 border-b">Harga (Rp)</th>
                    <th class="py-2 px-3 border-b w-12"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $index => $row)
                    <tr class="text-sm">
                        <td class="py-2 px-3 border-b">
                            <input type="text" wire:model.lazy="rows.{{ $index }}.nama_barang" class="w-full border-gray-300 rounded-md">
                            @error('rows.' . $index . '.nama_barang')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="py-2 px-3 border-b">
                            <input type="text" wire:model.lazy="rows.{{ $index }}.kategori" class="w-full border-gray-300 rounded-md">
                            @error('rows.' . $index . '.kategori')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="py-2 px-3 border-b">
                            <input type="number" wire:model.lazy="rows.{{ $index }}.jumlah" step="0.01" class="w-full border-gray-300 rounded-md">
                            @error('rows.' . $index . '.jumlah')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="py-2 px-3 border-b">
                            <input type="number" wire:model.lazy="rows.{{ $index }}.berat_barang" step="0.01" class="w-full border-gray-300 rounded-md">
                            @error('rows.' . $index . '.berat_barang')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="py-2 px-3 border-b">
                            <input type="number" wire:model.lazy="rows.{{ $index }}.harga" step="0.01" class="w-full border-gray-300 rounded-md">
                            @error('rows.' . $index . '.harga')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </td>
                        <td class="py-2 px-3 border-b text-center">
                            <button type="button" wire:click="removeRow({{ $index }})" class="text-red-500 hover:text-red-700">
                                &times;
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tombol Aksi --}}
    <div class="mt-4 flex justify-between">
        <button type="button" wire:click="addRow" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
            + Tambah Baris
        </button>
        <button type="button" wire:click="save" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Simpan
        </button>
    </div>
</div>