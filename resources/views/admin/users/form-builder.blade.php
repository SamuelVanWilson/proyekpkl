@extends('layouts.admin')

@section('title', 'Form Builder untuk ' . $user->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600">&larr; Kembali</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-2">Form Builder</h1>
    <p class="mt-1 text-base text-gray-600">Atur kolom formulir rekapitulasi untuk klien: <span class="font-semibold">{{ $user->name }}</span></p>
</div>

{{-- Kita akan gunakan sedikit Alpine.js untuk membuat form ini dinamis --}}
<script src="//unpkg.com/alpinejs" defer></script>

<div x-data='formBuilder(@json($config->columns ?? []))' class="bg-white p-6 rounded-xl shadow-sm">
    <form action="{{ route('admin.users.form-builder.store', $user) }}" method="POST">
        @csrf
        <div class="space-y-4">
            <template x-for="(field, index) in fields" :key="index">
                <div class="flex items-center space-x-3 p-3 border rounded-lg">
                    <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-3">
                        <input type="text" :name="`columns[${index}][name]`" x-model="field.name" placeholder="Nama Kolom (e.g., nomor_polisi)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <input type="text" :name="`columns[${index}][label]`" x-model="field.label" placeholder="Label Tampilan (e.g., Nomor Polisi)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <select :name="`columns[${index}][type]`" x-model="field.type" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="text">Teks</option>
                            <option value="number">Angka</option>
                            <option value="date">Tanggal</option>
                        </select>
                    </div>
                    <button type="button" @click="removeField(index)" class="text-red-500 hover:text-red-700 p-2">
                        <ion-icon name="trash-outline" class="text-xl"></ion-icon>
                    </button>
                </div>
            </template>
        </div>

        <div class="mt-6 flex justify-between">
            <button type="button" @click="addField()" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                + Tambah Kolom
            </button>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>

<script>
    function formBuilder(initialFields) {
        return {
            fields: initialFields.length > 0 ? initialFields : [{ name: '', label: '', type: 'text' }],
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
