@extends('layouts.client')

@section('title', 'Profil Saya')

@section('content')

{{-- Header Halaman --}}
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
    
    {{-- Grup Detail Akun --}}
    <div class="mt-6">
        <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase">Detail Akun</h2>
        <div class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">
            {{-- Item Email --}}
            <div class="p-4 flex justify-between items-center">
                <span class="font-medium text-gray-700">Email</span>
                <span class="text-gray-500">{{ Auth::user()->email }}</span>
            </div>
            {{-- Item Kode Unik --}}
            <div class="p-4 flex justify-between items-center">
                <span class="font-medium text-gray-700">Kode Unik</span>
                <span class="font-mono bg-gray-100 text-gray-600 text-sm px-2 py-1 rounded-md">{{ Auth::user()->kode_unik }}</span>
            </div>
        </div>
    </div>

    {{-- Grup Aksi --}}
    <div class="mt-6">
        <h2 class="px-4 text-xs font-semibold text-gray-500 uppercase">Aksi</h2>
        <div class="mt-2 bg-white rounded-xl border border-gray-200 divide-y divide-gray-200">
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
