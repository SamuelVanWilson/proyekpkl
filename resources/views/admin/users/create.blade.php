@extends('layouts.admin')
@section('title', 'Tambah Klien Baru')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 flex items-center mb-2">
        <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon>
        Kembali ke Manajemen Klien
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Tambah Klien Baru</h1>
    <p class="mt-1 text-base text-gray-600">Isi detail untuk mendaftarkan klien baru.</p>
</div>

<form action="{{ route('admin.users.store') }}" method="POST" class="mt-6">
    @csrf
    <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm space-y-6">
        {{-- Nama Lengkap --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Pengguna</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @else border-gray-300 @enderror">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alamat Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @else border-gray-300 @enderror">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nama Usaha --}}
        <div>
            <label for="nama_pabrik" class="block text-sm font-medium text-gray-700">Nama Pabrik</label>
            <input type="text" name="nama_pabrik" id="nama_pabrik" value="{{ old('nama_pabrik') }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('nama_pabrik') border-red-500 @else border-gray-300 @enderror">
             @error('nama_pabrik')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lokasi Pabrik --}}
         <div>
            <label for="lokasi_pabrik" class="block text-sm font-medium text-gray-700">Lokasi (Opsional)</label>
            <input type="text" name="lokasi_pabrik" id="lokasi_pabrik" value="{{ old('lokasi_pabrik') }}"
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('lokasi_pabrik') border-red-500 @else border-gray-300 @enderror">
            @error('lokasi_pabrik')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nomor Telepon --}}
         <div>
            <label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Handphone</label>
            <input type="tel" name="nomor_telepon" id="nomor_telepon" value="{{ old('nomor_telepon') }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('nomor_telepon') border-red-500 @else border-gray-300 @enderror">
            @error('nomor_telepon')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Pola Kode Unik --}}
        <div>
            <label for="kode_unik_pola" class="block text-sm font-medium text-gray-700">Pola Kode Unik</label>
            <select name="kode_unik_pola" id="kode_unik_pola" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="nama-usaha">[Nama Pengguna]-[Nama Pabrik]</option>
                <option value="usaha.tahun">[Nama Pabrik].[Tahun]</option>
                <option value="usaha.nama">[Nama Pabrik].[Nama Pengguna]</option>
                <option value="lokasi-nama">[Lokasi]-[Nama Pengguna]</option>
            </select>
             @error('kode_unik_pola')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status Akun --}}
        <div>
            <label for="is_active" class="block text-sm font-medium text-gray-700">Status Akun</label>
            <select name="is_active" id="is_active" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="1" selected>Aktif</option>
                <option value="0">Nonaktif</option>
            </select>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="mt-6 flex justify-end space-x-3">
        <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
            Batal
        </a>
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
            Simpan & Buat Kode Unik
        </button>
    </div>
</form>
@endsection

