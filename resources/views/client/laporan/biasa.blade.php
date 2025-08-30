@extends('layouts.client')

@section('title', 'Laporan Harian')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Laporan Harian (Mode Biasa)</h1>

        {{--
            Halaman ini merupakan versi sederhana dari sistem laporan harian.
            Anda dapat memasukkan data sesuai konfigurasi default yang telah
            ditentukan oleh administrator. Jika Anda ingin menggunakan fitur
            rekapitulasi lanjutan dengan tabel dinamis, silakan berlangganan
            paket premium dan kunjungi halaman “Laporan Advanced”.
        --}}

        @livewire('laporan.create-laporan')
    </div>
@endsection