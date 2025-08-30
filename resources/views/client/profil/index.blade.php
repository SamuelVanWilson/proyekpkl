@extends('layouts.client')

@section('title', 'Profil Saya')

@section('content')

@php
    // Ambil data user yang sedang login
    $user = Auth::user();
@endphp

{{-- Header Halaman --}}
<div class="bg-white pt-10 pb-6 px-4 safe-area-top border-b border-gray-200 sticky top-0 z-10">
    <h1 class="text-3xl font-bold text-gray-900 text-center">
        Profil
    </h1>
</div>

{{-- Konten Profil --}}
<div class="p-4">
    {{-- Kartu Informasi Pengguna --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 flex items-center space-x-4">
            <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-2xl font-bold">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <p class="font-semibold text-lg text-gray-900">{{ $user->name }}</p>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    {{-- Detail Data Diri --}}
    <div class="mt-6">
        <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Data Diri</h2>
        {{-- Form untuk memperbarui profil --}}
        <form action="{{ route('client.profil.update') }}" method="POST" class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">
            @csrf
            <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm" required>
                @error('name') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm" required>
                @error('email') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                @error('tanggal_lahir') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <input type="text" name="alamat" value="{{ old('alamat', $user->alamat) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                @error('alamat') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $user->pekerjaan) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                @error('pekerjaan') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                @error('nomor_telepon') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="p-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Aksi & Pengaturan --}}
    <div class="mt-6">
        <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengaturan</h2>
        <div class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">

            {{-- Tombol Install PWA --}}
            <button id="install-app-button" onclick="promptInstall()" style="display: none;" class="p-4 flex justify-between items-center w-full text-left">
                <span class="font-medium text-blue-600">Install Aplikasi</span>
                <ion-icon name="download-outline" class="text-gray-400 text-xl"></ion-icon>
            </button>

            {{-- Tombol Fullscreen Toggle --}}
            <button onclick="toggleFullscreen()" class="p-4 flex justify-between items-center w-full text-left">
                <span class="font-medium text-blue-600">Mode Fullscreen</span>
                <ion-icon name="scan-outline" class="text-gray-400 text-xl"></ion-icon>
            </button>

            {{-- Tombol Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-4 flex justify-between items-center w-full text-left">
                    <span class="font-medium text-red-500">Keluar</span>
                    <ion-icon name="log-out-outline" class="text-gray-400 text-xl"></ion-icon>
                </button>
            </form>
            {{-- Tombol Nonaktifkan Akun --}}
            <form action="{{ route('client.profil.deactivate') }}" method="POST" onsubmit="return confirm('Menonaktifkan akun akan menghanguskan langganan Anda dan tidak dapat digunakan kembali. Lanjutkan?');">
                @csrf
                <button type="submit" class="p-4 flex justify-between items-center w-full text-left">
                    <span class="font-medium text-red-600">Nonaktifkan Akun</span>
                    <ion-icon name="alert-circle-outline" class="text-gray-400 text-xl"></ion-icon>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
