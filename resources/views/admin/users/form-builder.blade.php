@extends('layouts.admin')

@section('title', 'Form Builder untuk ' . $user->name)

@push('scripts')
{{-- Menggunakan Alpine.js untuk manajemen form dinamis --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
{{-- PERBAIKAN: Inisialisasi Alpine.js dengan data dari variabel $config yang benar --}}
<div class="p-4 sm:p-6" x-data='{ rincian: @json($config->columns["rincian"] ?? []), rekap: @json($config->columns["rekap"] ?? []) }'>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            {{-- Header yang disesuaikan untuk admin --}}
            <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Daftar Klien</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Atur Kolom Laporan</h1>
            <p class="text-gray-600 mt-1">Anda sedang mengubah konfigurasi untuk: <span class="font-semibold">{{ $user->name }}</span></p>
        </div>

        {{-- PERBAIKAN: Mengarahkan form ke route admin yang benar --}}
        <form action="{{ route('admin.users.form-builder.store', $user) }}" method="POST">
            @csrf

            {{-- Konfigurasi Tabel Rincian (Struktur sama     dengan client) --}}
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200 mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-2">
                    <h2 class="text-lg font-semibold text-gray-700">Kolom Tabel Rincian</h2>
                    <button type="button" @click="rincian.push({ name: '', label: '', type: 'text' })" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Tambah Kolom Rincian
                    </button>1
                </div>
                <div class="space-y-4">
                    <template x-for="(col, index) in rincian" :key="index">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end p-3 bg-gray-50 rounded-lg border">
                            {{-- Nama Kolom (col-span-2) --}}
                            <div class="sm:col-span-2">
                                <label class="text-sm font-medium text-gray-500">Nama Kolom</label>
                                {{-- PERUBAHAN: Input 'label' dihapus --}}
                                <input type="text" :name="`rincian[${index}][name]`" x-model="col.label" class="input-modern" placeholder="e.g., Berat Kotor" required>
                            </div>
                            {{-- Tipe & Hapus --}}
                            <div class="flex items-center justify-between">
                                <div class="w-full pr-2">
                                    <label class="text-sm font-medium text-gray-500">Tipe Data</label>
                                    <select :name="`rincian[${index}][type]`" x-model="col.type" class="input-modern">
                                        <option value="text">Teks</option>
                                        <option value="number">Angka</option>
                                    </select>
                                </div>
                                <button type="button" @click="rincian.splice(index, 1)" class="px-3 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md mt-1">Hapus</button>
                            </div>
                        </div>
                    </template>
                    <p x-show="rincian.length === 0" class="text-center text-gray-500 py-4">Belum ada kolom rincian.</p>
                </div>
            </div>

            {{-- Konfigurasi Formulir Rekapitulasi (Struktur sama dengan client) --}}
            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-2">
                    <h2 class="text-lg font-semibold text-gray-700">Kolom Formulir Rekapitulasi</h2>
                    <button type="button" @click="rekap.push({ name: '', label: '', type: 'text', formula: '', readonly: false, default_value: '' })" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Tambah Kolom Rekap
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="p-4 bg-sky-50 border border-sky-200 rounded-lg text-sm text-sky-800">
                        <p>Kolom yang memiliki <strong>Rumus</strong> akan otomatis bersifat <strong>Read-Only</strong>.</p>
                    </div>
                    <template x-for="(col, index) in rekap" :key="index">
                        <div class="flex flex-col gap-3 p-3 bg-gray-50 rounded-lg border">
                            {{-- Baris 1: Nama Kolom & Tipe Data --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Nama Kolom</label>
                                    {{-- PERUBAHAN: Input 'label' dihapus --}}
                                    <input type="text" :name="`rekap[${index}][name]`" x-model="col.label" class="input-modern" placeholder="e.g., Total Bersih" required>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tipe Data</label>
                                    <select :name="`rekap[${index}][type]`" x-model="col.type" class="input-modern">
                                        <option value="text">Teks</option>
                                        <option value="number">Angka (Biasa)</option>
                                        <option value="date">Tanggal</option>
                                        <option value="rupiah">Rupiah (Rp)</option>
                                        <option value="dollar">Dollar ($)</option>
                                        <option value="kg">Kilogram (Kg)</option>
                                        <option value="g">Gram (g)</option>
                                    </select>
                                </div>
                            </div>
                            {{-- Baris 2: Nilai Default & Rumus --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Nilai Default (Opsional)</label>
                                    <input type="text" :name="`rekap[${index}][default_value]`" x-model="col.default_value" class="input-modern" placeholder="Kosongkan jika tidak ada">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Rumus (Gunakan underscore, cth: total_aset)</label>
                                    <input type="text" :name="`rekap[${index}][formula]`" x-model="col.formula" class="input-modern font-mono text-xs" placeholder="e.g., SUM(total)">
                                </div>
                            </div>
                            {{-- Baris 3: Aksi --}}
                            <div class="flex justify-between items-center pt-1">
                                <div class="flex items-center">
                                    <input type="hidden" :name="`rekap[${index}][readonly]`" value="0">
                                    <input type="checkbox" :name="`rekap[${index}][readonly]`" value="1" :id="`readonly_${index}`" x-model="col.readonly" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <label :for="`readonly_${index}`" class="ml-2 block text-sm text-gray-700">Read Only</label>
                                </div>
                                <button type="button" @click="rekap.splice(index, 1)" class="px-3 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md">Hapus</button>
                            </div>
                        </div>
                    </template>
                    <p x-show="rekap.length === 0" class="text-center text-gray-500 py-4">Belum ada kolom rekapitulasi.</p>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                {{-- Tombol Batal yang disesuaikan untuk admin --}}
                <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto text-center px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection