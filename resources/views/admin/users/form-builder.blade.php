@extends('layouts.admin')

@section('title', 'Form Builder untuk ' . $user->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-700 transition-colors">&larr; Kembali ke Daftar Klien</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-2">Form Builder</h1>
    <p class="mt-1 text-base text-gray-600">Atur kolom formulir rekapitulasi untuk klien: <span class="font-semibold">{{ $user->name }}</span></p>
</div>

{{-- Memuat Alpine.js jika belum ada di layout utama --}}
<script src="//unpkg.com/alpinejs" defer></script>

{{--
    PERBAIKAN:
    - Kita passing data error & input lama dari Laravel ke Alpine.js
    - @json($errors->get('columns.*')) akan mengambil semua error yang berhubungan dengan array 'columns'
    - @json(old('columns', $config->columns ?? [])) akan mengambil input lama, atau data dari config jika tidak ada input lama.
--}}
<div x-data='formBuilder(@json(old('columns', $config->columns ?? [])), @json($errors->get('columns.*')))' class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200">
    <form action="{{ route('admin.users.form-builder.store', $user) }}" method="POST">
        @csrf
        <div class="space-y-4">
            <template x-for="(field, index) in fields" :key="index">
                <div class="p-4 border rounded-lg" :class="{ 'border-red-400': errors[`columns.${index}.name`] || errors[`columns.${index}.label`] }">
                    <div class="flex items-start space-x-3">
                        <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Input untuk Nama Kolom --}}
                            <div>
                                <label :for="`name_${index}`" class="block text-sm font-medium text-gray-700">Nama Kolom (unik, tanpa spasi)</label>
                                <input type="text" :id="`name_${index}`" :name="`columns[${index}][name]`" x-model="field.name" placeholder="cth: nomor_polisi"
                                       class="mt-1 block w-full rounded-md shadow-sm text-sm"
                                       :class="errors[`columns.${index}.name`] ? 'border-red-500 ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'">
                                <template x-if="errors[`columns.${index}.name`]">
                                    <p x-text="errors[`columns.${index}.name`][0]" class="mt-1 text-xs text-red-600"></p>
                                </template>
                            </div>

                            {{-- Input untuk Label Tampilan --}}
                            <div>
                                <label :for="`label_${index}`" class="block text-sm font-medium text-gray-700">Label Tampilan</label>
                                <input type="text" :id="`label_${index}`" :name="`columns[${index}][label]`" x-model="field.label" placeholder="cth: Nomor Polisi"
                                       class="mt-1 block w-full rounded-md shadow-sm text-sm"
                                       :class="errors[`columns.${index}.label`] ? 'border-red-500 ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'">
                                <template x-if="errors[`columns.${index}.label`]">
                                    <p x-text="errors[`columns.${index}.label`][0]" class="mt-1 text-xs text-red-600"></p>
                                </template>
                            </div>

                            {{-- Pilihan Tipe Input --}}
                            <div>
                                <label :for="`type_${index}`" class="block text-sm font-medium text-gray-700">Tipe Input</label>
                                <select :id="`type_${index}`" :name="`columns[${index}][type]`" x-model="field.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="text">Teks</option>
                                    <option value="number">Angka</option>
                                    <option value="date">Tanggal</option>
                                </select>
                            </div>
                        </div>
                        {{-- Tombol Hapus --}}
                        <div class="pt-6">
                             <button type="button" @click="removeField(index)" class="text-gray-400 hover:text-red-500 p-2 rounded-full transition-colors">
                                <ion-icon name="trash-outline" class="text-xl"></ion-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="mt-6 flex justify-between items-center">
            <button type="button" @click="addField()" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300 transition-colors">
                + Tambah Kolom
            </button>
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition-colors">
                Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>

<script>
    function formBuilder(initialFields, validationErrors) {
        return {
            fields: initialFields.length > 0 ? initialFields : [{ name: '', label: '', type: 'text' }],
            errors: validationErrors || {},
            addField() {
                this.fields.push({ name: '', label: '', type: 'text' });
            },
            removeField(index) {
                this.fields.splice(index, 1);
            }
        }
    }
</script>
@endsection
