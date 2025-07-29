@extends('layouts.admin')
@section('title', 'Edit Klien: ' . $user->name)

@section('content')
<script src="//unpkg.com/alpinejs" defer></script>

<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 flex items-center mb-2">
        <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon>
        Kembali ke Manajemen Klien
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Edit Klien</h1>
    <p class="mt-1 text-base text-gray-600">Perbarui detail untuk klien <span class="font-semibold">{{ $user->name }}</span>.</p>
</div>

{{-- Blok untuk menampilkan kode unik yang baru dibuat (hanya muncul setelah user dibuat) --}}
@if (session('new_kode_unik'))
<div x-data="{ copied: false }" class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <ion-icon name="keypad-outline" class="text-xl text-yellow-500"></ion-icon>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-semibold text-yellow-800">Kode Unik Baru Telah Dibuat!</p>
            <p class="mt-1 text-sm text-yellow-700">Harap salin dan kirimkan kode ini kepada klien. Kode ini hanya ditampilkan sekali.</p>
            <div class="mt-3 flex items-center bg-gray-100 p-2 rounded-md">
                <span class="font-mono text-gray-700" id="kodeUnik">{{ session('new_kode_unik') }}</span>
                <button @click="navigator.clipboard.writeText('{{ session('new_kode_unik') }}'); copied = true; setTimeout(() => copied = false, 2000)" class="ml-auto bg-gray-200 hover:bg-gray-300 text-gray-600 p-1.5 rounded-md">
                    <span x-show="!copied"><ion-icon name="copy-outline"></ion-icon></span>
                    <span x-show="copied" class="text-green-600"><ion-icon name="checkmark-outline"></ion-icon></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<form action="{{ route('admin.users.update', $user) }}" method="POST" class="mt-6">
    @csrf
    @method('PUT')
    <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm space-y-6">
        {{-- Nama Lengkap --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nama Pengguna</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @else border-gray-300 @enderror">
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Alamat Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @else border-gray-300 @enderror">
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nama Usaha --}}
        <div>
            <label for="nama_pabrik" class="block text-sm font-medium text-gray-700">Nama Pabrik</label>
            <input type="text" name="nama_pabrik" id="nama_pabrik" value="{{ old('nama_pabrik', $user->nama_pabrik) }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('nama_pabrik') border-red-500 @else border-gray-300 @enderror">
             @error('nama_pabrik')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Lokasi --}}
        <div>
            <label for="lokasi_pabrik" class="block text-sm font-medium text-gray-700">Lokasi</label>
            <input type="text" name="lokasi_pabrik" id="lokasi_pabrik" value="{{ old('lokasi_pabrik', $user->lokasi_pabrik) }}"
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('lokasi_pabrik') border-red-500 @else border-gray-300 @enderror">
             @error('lokasi_pabrik')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nomor Telepon --}}
        <div>
            <label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Handphone</label>
            <input type="tel" name="nomor_telepon" id="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" required
                   class="mt-1 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('nomor_telepon') border-red-500 @else border-gray-300 @enderror">
            @error('nomor_telepon')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status Akun --}}
        <div>
            <label for="is_active" class="block text-sm font-medium text-gray-700">Status Akun</label>
            <select name="is_active" id="is_active" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="1" @selected(old('is_active', $user->is_active))>Aktif</option>
                <option value="0" @selected(!old('is_active', $user->is_active))>Nonaktif</option>
            </select>
        </div>

        {{-- Atur Ulang Kode Unik --}}
        <div class="pt-4 border-t border-gray-200">
            <label for="kode_unik_baru" class="block text-sm font-medium text-gray-700">Atur Ulang Kode Unik (Opsional)</label>
            <p class="text-xs text-gray-500 mt-1">Isi kolom ini hanya jika Anda ingin mengganti kode unik klien.</p>
            <input type="text" name="kode_unik_baru" id="kode_unik_baru"
                   class="mt-2 block w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('kode_unik_baru') border-red-500 @else border-gray-300 @enderror">
            @error('kode_unik_baru')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="mt-6 flex justify-end space-x-3">
        <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
            Kembali
        </a>
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
            Perbarui Klien
        </button>
    </div>
</form>
@endsection
