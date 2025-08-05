@extends('layouts.client')

@section('title', 'Atur Laporan')

@section('content')
<div class="p-4 md:p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Atur Laporan</h1>
        <p class="mt-1 text-base text-gray-600">Kustomisasi kolom pada tabel rincian dan formulir rekapitulasi Anda.</p>
    </div>

    {{-- Kita akan menggunakan kembali script dan struktur dari form builder admin --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    <form action="{{ route('client.laporan.form-builder.store') }}" method="POST">
        @csrf
        <div class="space-y-8">
            {{-- Konfigurasi Tabel Rincian --}}
            <div x-data='formSection(@json(old('columns.rincian', $config->columns['rincian'] ?? [])))' class="bg-white p-6 rounded-xl shadow-sm">
                <h2 class="text-xl font-bold text-gray-800">1. Kolom Tabel Rincian</h2>
                <p class="text-sm text-gray-500 mt-1">Ini adalah kolom yang akan Anda isi berulang kali.</p>
                <div class="mt-4 space-y-4">
                    <template x-for="(field, index) in fields" :key="index">
                        <div class="flex items-center space-x-3 p-3 border rounded-lg">
                            <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="text" :name="`columns[rincian][${index}][name]`" x-model="field.name" placeholder="Nama Kolom (unik, tanpa spasi)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <input type="text" :name="`columns[rincian][${index}][label]`" x-model="field.label" placeholder="Nama Tampilan Kolom" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            </div>
                            <button type="button" @click="removeField(index)" class="text-red-500 hover:text-red-700 p-2"><ion-icon name="trash-outline" class="text-xl"></ion-icon></button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addField()" class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Kolom</button>
            </div>

            {{-- Konfigurasi Formulir Rekapitulasi --}}
            <div x-data='formSection(@json(old('columns.rekap', $config->columns['rekap'] ?? [])))' class="bg-white p-6 rounded-xl shadow-sm">
                <h2 class="text-xl font-bold text-gray-800">2. Kolom Formulir Rekapitulasi</h2>
                <p class="text-sm text-gray-500 mt-1">Ini adalah kolom ringkasan. Anda bisa menambahkan rumus perhitungan di sini.</p>
                <div class="mt-4 space-y-4">
                    <template x-for="(field, index) in fields" :key="index">
                        <div class="p-3 border rounded-lg space-y-3">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="text" :name="`columns[rekap][${index}][name]`" x-model="field.name" placeholder="Nama Kolom (unik, tanpa spasi)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <input type="text" :name="`columns[rekap][${index}][label]`" x-model="field.label" placeholder="Nama Tampilan Kolom" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                             </div>
                             <input type="text" :name="`columns[rekap][${index}][formula]`" x-model="field.formula" placeholder="Isi dengan rumus, contoh: SUM(jumlah) * harga" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </template>
                </div>
                <button type="button" @click="addField()" class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Kolom</button>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    function formSection(initialFields) {
        return {
            fields: initialFields && initialFields.length > 0 ? initialFields : [{ name: '', label: '', type: 'text', formula: '', readonly: false }],
            addField() {
                this.fields.push({ name: '', label: '', type: 'text', formula: '', readonly: false });
            },
            removeField(index) {
                this.fields.splice(index, 1);
            }
        }
    }
</script>
@endsection
