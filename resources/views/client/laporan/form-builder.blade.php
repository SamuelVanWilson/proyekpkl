@extends('layouts.client')

@section('title', 'Atur Kolom Laporan')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
<div class="p-4 sm:p-6" x-data='{ rincian: @json($columns["rincian"] ?? []), rekap: @json($columns["rekap"] ?? []) }'>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Atur Kolom Laporan</h1>
            <p class="text-gray-600 mt-1">Sesuaikan kolom pada tabel rincian dan formulir rekapitulasi sesuai kebutuhan Anda.</p>
        </div>

        <form action="{{ route('client.laporan.form-builder.save') }}" method="POST">
            @csrf

            {{-- Konfigurasi Tabel Rincian --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Kolom Tabel Rincian</h2>
                    <button type="button" @click="rincian.push({ name: '', label: '', type: 'text' })" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Tambah Kolom Rincian
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(col, index) in rincian" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-10 gap-3 items-center p-3 bg-gray-50 rounded-lg">
                            <div class="md:col-span-4">
                                <label class="text-sm font-medium text-gray-500">Nama Kolom (unik)</label>
                                <input type="text" :name="`rincian[${index}][name]`" x-model="col.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., berat_kotor">
                            </div>
                            <div class="md:col-span-3">
                                <label class="text-sm font-medium text-gray-500">Label Tampilan</label>
                                <input type="text" :name="`rincian[${index}][label]`" x-model="col.label" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., Berat Kotor">
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm font-medium text-gray-500">Tipe Data</label>
                                <select :name="`rincian[${index}][type]`" x-model="col.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="text">Teks</option>
                                    <option value="number">Angka</option>
                                </select>
                            </div>
                            <div class="text-right pt-5">
                                <button type="button" @click="rincian.splice(index, 1)" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Konfigurasi Formulir Rekapitulasi --}}
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Kolom Formulir Rekapitulasi</h2>
                    <button type="button" @click="rekap.push({ name: '', label: '', type: 'text', formula: '', readonly: false })" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Tambah Kolom Rekap
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(col, index) in rekap" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-10 gap-3 items-start p-3 bg-gray-50 rounded-lg">
                            <div class="md:col-span-3">
                                <label class="text-sm font-medium text-gray-500">Nama Kolom (unik)</label>
                                <input type="text" :name="`rekap[${index}][name]`" x-model="col.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., total_bersih">
                            </div>
                            <div class="md:col-span-3">
                                <label class="text-sm font-medium text-gray-500">Label Tampilan</label>
                                <input type="text" :name="`rekap[${index}][label]`" x-model="col.label" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="e.g., Total Bersih">
                            </div>
                            <div class="md:col-span-3">
                                <label class="text-sm font-medium text-gray-500">Tipe Data</label>
                                <select :name="`rekap[${index}][type]`" x-model="col.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="text">Teks</option>
                                    <option value="number">Angka</option>
                                    <option value="date">Tanggal</option>
                                </select>
                            </div>
                            <div class="text-right pt-5">
                                <button type="button" @click="rekap.splice(index, 1)" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </div>
                            <div class="md:col-span-9">
                                <label class="text-sm font-medium text-gray-500">Rumus (opsional)</label>
                                <input type="text" :name="`rekap[${index}][formula]`" x-model="col.formula" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm font-mono text-sm" placeholder="e.g., SUM(total) atau SUBT(total_bruto, potongan)">
                            </div>
                            {{-- FITUR READONLY BARU --}}
                            <div class="md:col-span-10 flex items-center pt-2">
                                <input type="hidden" :name="`rekap[${index}][readonly]`" :value="col.readonly ? 1 : 0">
                                <input type="checkbox" :id="`readonly_${index}`" x-model="col.readonly" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                                <label :for="`readonly_${index}`" class="ml-2 block text-sm text-gray-900">Read Only (Tidak bisa diedit manual)</label>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('client.laporan.harian') }}" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 mr-3">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
