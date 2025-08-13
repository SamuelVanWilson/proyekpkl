@extends('layouts.client')

@section('title', 'Profil Saya')

@section('content')

<div class="bg-white pt-10 pb-6 px-4 safe-area-top border-b border-gray-200 sticky top-0 z-10">
    <h1 class="text-3xl font-bold text-gray-900 text-center">
        Profil
    </h1>
</div>

{{-- Konten Profil --}}
<div class="p-4">
    {{-- Grup Informasi Akun --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="p-4 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xl font-bold">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div>
                <p class="font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                <p class="text-sm text-gray-500">{{ Auth::user()->nama_pabrik ?? 'Klien Personal' }}</p>
            </div>
        </div>
    </div>
    
    {{-- PERBAIKAN: Grup Detail Akun (Kode unik dihapus) --}}
    <div class="mt-6">
        <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase">Detail Kontak</h2>
        <div class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">
            <div class="p-4 flex justify-between items-center">
                <span class="font-medium text-gray-700">Email</span>
                <span class="text-gray-600 text-sm">{{ Auth::user()->email }}</span>
            </div>
            <div class="p-4 flex justify-between items-center">
                <span class="font-medium text-gray-700">Nomor Telepon</span>
                <span class="text-gray-600 text-sm">{{ Auth::user()->nomor_telepon }}</span>
            </div>
        </div>
    </div>

    {{-- Grup Aksi --}}
    <div class="mt-6">
        <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase">Aksi</h2>
        <div class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">
            
            {{-- TOMBOL INSTALL PWA BARU --}}
            {{-- Tombol ini defaultnya tersembunyi dan akan ditampilkan oleh JavaScript jika PWA bisa di-install --}}
            <button id="install-app-button" onclick="promptInstall()" style="display: none;" class="p-4 flex justify-between items-center w-full text-left">
                <span class="font-medium text-blue-500">Install Aplikasi</span>
                <ion-icon name="download-outline" class="text-gray-400 text-xl"></ion-icon>
            </button>
            
            {{-- Tombol Minta Ganti Kode --}}
            <a href="{{ $whatsappUrl }}" target="_blank" class="p-4 flex justify-between items-center w-full text-left">
                <span class="font-medium text-blue-500">Minta Ganti Kode Unik</span>
                <ion-icon name="logo-whatsapp" class="text-gray-400 text-xl"></ion-icon>
            </a>
            
            {{-- Tombol Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-4 flex justify-between items-center w-full text-left">
                    <span class="font-medium text-red-500">Keluar</span>
                    <ion-icon name="log-out-outline" class="text-gray-400 text-xl"></ion-icon>
                </button>
            </form>
        </div>
    </div>

    {{-- Footer Versi Aplikasi --}}
    <div class="mt-8 text-center text-xs text-gray-400">
        <p>Aplikasi Laporan v1.0.0</p>
    </div>
</div>
    
@endsection
