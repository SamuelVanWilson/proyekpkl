@extends('layouts.admin')

@section('title', 'Tambah Klien Baru')

@section('content')
<div>
    <h1 class="text-2xl font-semibold text-gray-900">Tambah Klien Baru</h1>
    <p class="mt-1 text-sm text-gray-500">Isi detail klien yang akan berlangganan.</p>
</div>

<form action="{{ route('admin.users.store') }}" method="POST" class="mt-6 bg-white shadow sm:rounded-lg p-6 space-y-6">
    @csrf
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
        <div class="mt-1">
            <input type="text" name="name" id="name" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
        <div class="mt-1">
            <input type="email" name="email" id="email" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>
    <div>
        <label for="nama_pabrik" class="block text-sm font-medium text-gray-700">Nama Usaha (Opsional)</label>
        <div class="mt-1">
            <input type="text" name="nama_pabrik" id="nama_pabrik" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700">Status Akun</label>
        <select name="is_active" class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm">
            <option value="1" selected>Aktif</option>
            <option value="0">Nonaktif</option>
        </select>
    </div>
    <div class="pt-5">
        <div class="flex justify-end">
            <a href="{{ route('admin.users.index') }}" class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Batal
            </a>
            <button type="submit" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                Simpan Klien
            </button>
        </div>
    </div>
</form>
@endsection
