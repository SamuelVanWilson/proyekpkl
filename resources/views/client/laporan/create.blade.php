@extends('layouts.client')

@section('title', 'Buat Laporan Baru')

@section('content')
<div class="p-4">
    <div class="max-w-3xl mx-auto">
        {{-- Header Halaman --}}
        <div class="mb-6">
            <a href="{{ route('client.laporan.index') }}" class="text-sm text-gray-500 flex items-center mb-2">
                <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon>
                Kembali ke Daftar Laporan
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Buat Laporan Baru</h1>
            <p class="mt-1 text-base text-gray-600">Isi data di bawah ini, total akan dihitung otomatis.</p>
        </div>

        {{-- Memuat Komponen Livewire --}}
        @livewire('laporan.create-laporan')

    </div>
</div>
@endsection
