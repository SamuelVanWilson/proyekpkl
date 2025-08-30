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

        {{-- Gunakan komponen SimpleTable untuk laporan biasa (tanpa rekapitulasi).
             Jika $reportId tersedia (mode edit), oper ke komponen. --}}
        @isset($reportId)
            @livewire('laporan.simple-table', ['reportId' => $reportId])
        @else
            @livewire('laporan.simple-table')
        @endisset
    </div>
@endsection