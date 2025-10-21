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

{{-- Tampilkan pesan sukses jika ada --}}
@if(session('success'))
    <div class="px-4 py-3 mt-4 mx-4 bg-green-100 border border-green-200 text-green-700 rounded-lg text-sm">
        {{ session('success') }}
    </div>
@endif

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

{{-- Detail Data Diri (hanya tampilan) --}}
<div class="mt-6">
    <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Data Diri</h2>
    <div class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">
        <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Nama Lengkap</span>
            <span class="text-sm text-gray-900">{{ $user->name }}</span>
        </div>
        <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Email</span>
            <span class="text-sm text-gray-900">{{ $user->email }}</span>
        </div>
        @if($user->tanggal_lahir)
        <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Tanggal Lahir</span>
            <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($user->tanggal_lahir)->translatedFormat('d F Y') }}</span>
        </div>
        @endif
        @if($user->alamat)
        <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Alamat</span>
            <span class="text-sm text-gray-900">{{ $user->alamat }}</span>
        </div>
        @endif
        @if($user->pekerjaan)
        <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Pekerjaan</span>
            <span class="text-sm text-gray-900">{{ $user->pekerjaan }}</span>
        </div>
        @endif
        @if($user->nomor_telepon)
        <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Nomor Telepon</span>
            <span class="text-sm text-gray-900">{{ $user->nomor_telepon }}</span>
        </div>
        @endif
        <div class="p-4 flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">Status Langganan</span>
            @if($user->subscription_plan == null)
                <span class="text-sm text-gray-900 bg-gray-100 px-2 inline-flex items-center rounded-full">Gratis</span>
            @else
                <span class="text-sm text-blue-800 bg-blue-100 px-2 inline-flex items-center rounded-full">{{ $user->subscription_plan }}</span>
            @endif
        </div>
    </div>
</div>

    {{-- Aksi & Pengaturan --}}
    <div class="mt-6">
        <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengaturan</h2>
        <div class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">

            {{-- Tombol Install PWA --}}
            <button id="install-app-button" onclick="promptInstall()" style="display: none;" class="p-4 flex justify-between items-center w-full text-left">
                <span class="font-medium text-green-600">Install Aplikasi</span>
                <ion-icon name="download-outline" class="text-gray-400 text-xl"></ion-icon>
            </button>

            <a href="{{ route('client.profil.edit') }}" class="p-4 flex justify-between items-center w-full text-left">
                <span class="font-medium text-green-600">Ubah Data</span>
                <ion-icon name="settings-outline" class="text-gray-400 text-xl"></ion-icon>
            </a>

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
