@extends('layouts.admin')

@section('title', 'Form Builder untuk ' . $user->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600">&larr; Kembali</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-2">Form Builder Laporan</h1>
    <p class="mt-1 text-base text-gray-600">Atur struktur laporan untuk: <span class="font-semibold">{{ $user->name }}</span></p>
</div>

<script src="//unpkg.com/alpinejs" defer></script>

<form action="{{ route('admin.users.form-builder.store', $user) }}" method="POST">
    @csrf
    <div class="space-y-8">
        {{-- Konfigurasi Tabel Rincian --}}
        <div x-data='formSection(@json(old('columns.rincian', $config->columns['rincian'] ?? [])))' class="bg-white p-6 rounded-xl shadow-sm">
            <h2 class="text-xl font-bold text-gray-800">1. Konfigurasi Tabel Rincian</h2>
            <p class="text-sm text-gray-500 mt-1">Atur kolom-kolom yang akan diisi berulang kali (seperti baris di Excel).</p>
            <div class="mt-4 space-y-4">
                <template x-for="(field, index) in fields" :key="index">
                    <div class="p-3 border rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-3">
                                <input type="text" :name="`columns[rincian][${index}][name]`" x-model="field.name" placeholder="Nama Kolom (e.g., qty_pcs)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <input type="text" :name="`columns[rincian][${index}][label]`" x-model="field.label" placeholder="Label Tampilan (e.g., Jumlah (Pcs))" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <select :name="`columns[rincian][${index}][type]`" x-model="field.type" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="text">Teks</option>
                                    <option value="number">Angka</option>
                                </select>
                            </div>
                            <button type="button" @click="removeField(index)" class="text-red-500 hover:text-red-700 p-2"><ion-icon name="trash-outline" class="text-xl"></ion-icon></button>
                        </div>
                    </div>
                </template>
            </div>
            <button type="button" @click="addField()" class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Kolom Rincian</button>
        </div>

        {{-- Konfigurasi Formulir Rekapitulasi --}}
        <div x-data='formSection(@json(old('columns.rekap', $config->columns['rekap'] ?? [])))' class="bg-white p-6 rounded-xl shadow-sm">
            <h2 class="text-xl font-bold text-gray-800">2. Konfigurasi Formulir Rekapitulasi</h2>
            <p class="text-sm text-gray-500 mt-1">Gunakan `SUM(nama_kolom_rincian)` dan operator (`+`, `-`, `*`, `/`) untuk perhitungan otomatis.</p>
            <div class="mt-4 space-y-4">
                <template x-for="(field, index) in fields" :key="index">
                    <div class="p-3 border rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="text" :name="`columns[rekap][${index}][name]`" x-model="field.name" placeholder="Nama Kolom (e.g., total_penjualan)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <input type="text" :name="`columns[rekap][${index}][label]`" x-model="field.label" placeholder="Label Tampilan (e.g., Total Penjualan)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <select :name="`columns[rekap][${index}][type]`" x-model="field.type" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="text">Teks</option>
                                <option value="number">Angka</option>
                                <option value="date">Tanggal</option>
                            </select>
                            {{-- PERBAIKAN: Checkbox untuk Read-only --}}
                            <div class="flex items-center justify-center">
                                <input type="checkbox" :name="`columns[rekap][${index}][readonly]`" x-model="field.readonly" :value="true" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label class="ml-2 block text-sm text-gray-900">Read-only (Hanya Lihat)</label>
                            </div>
                        </div>
                        <div class="mt-3">
                             <input type="text" :name="`columns[rekap][${index}][formula]`" x-model="field.formula" placeholder="Rumus (e.g., SUM(qty_pcs) * harga_satuan)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        </div>
                    </div>
                </template>
            </div>
             <button type="button" @click="addField()" class="mt-4 text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah Kolom Rekapitulasi</button>
        </div>
    </div>
    <div class="mt-6 flex justify-end">
        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
            Simpan Konfigurasi
        </button>
    </div>
</form>

<script>
    function formSection(initialFields) {
        return {
            fields: initialFields && initialFields.length > 0 ? initialFields : [{ name: '', label: '', type: 'text', formula: '' }],
            addField() {
                this.fields.push({ name: '', label: '', type: 'text', formula: '' });
            },
            removeField(index) {
                this.fields.splice(index, 1);
            }
        }
    }
</script>
@endsection
