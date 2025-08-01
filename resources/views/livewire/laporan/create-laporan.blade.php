<div>
    {{-- Menggunakan form dengan wire:submit.prevent untuk mencegah submit tradisional --}}
    <form wire:submit.prevent="simpanLaporan">
        <div class="space-y-6">
            <div class="bg-white p-4 rounded-xl shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">Formulir Rekapitulasi</h2>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- LOOPING UNTUK MEMBUAT FORM SESUAI BLUEPRINT --}}
                    @foreach ($formConfig as $field)
                        <div>
                            <label for="{{ $field['name'] }}" class="text-sm font-medium text-gray-600">{{ $field['label'] }}</label>
                            <input
                                type="{{ $field['type'] }}"
                                wire:model.defer="rekapData.{{ $field['name'] }}"
                                id="{{ $field['name'] }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    @endforeach

                </div>
            </div>

            {{-- Bagian Hasil Kalkulasi (Read-only) --}}
            <div class="bg-white p-4 rounded-xl shadow-sm">
                 <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">Hasil Kalkulasi Otomatis</h2>
                 <div class="mt-4 text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-gray-600">Jumlah Karung:</span> <span class="font-semibold">{{ $jumlah_karung }} Karung</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Total Bruto:</span> <span class="font-semibold">{{ number_format($total_bruto, 0, ',', '.') }} Kg</span></div>
                    <div class="flex justify-between"><span class="text-gray-600">Total Netto:</span> <span class="font-semibold">{{ number_format($total_netto, 0, ',', '.') }} Kg</span></div>
                    <hr class="my-1">
                    <div class="flex justify-between"><span class="text-gray-600">Harga Bruto:</span> <span class="font-semibold">Rp {{ number_format($harga_bruto, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-lg font-bold"><span class="text-gray-800">Total Uang:</span> <span class="text-green-600">Rp {{ number_format($total_uang, 0, ',', '.') }}</span></div>
                 </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('client.laporan.index') }}" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">Batal</a>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Simpan Laporan
                </button>
            </div>
        </div>
    </form>
</div>
