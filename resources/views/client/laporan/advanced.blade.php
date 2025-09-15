@extends('layouts.client')

@section('title', 'Laporan Advanced')

@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-semibold mb-4">Laporan Advanced</h1>

        {{--
            Halaman ini menampilkan fitur laporan lanjutan. Untuk saat ini
            komponen Livewire yang sama dengan laporan harian biasa digunakan.
            Admin dapat menambahkan tabel rincian dan formulir rekapitulasi
            khusus melalui fitur form builder. Pastikan Anda telah
            berlangganan agar middleware “subscribed” mengizinkan akses ke
            halaman ini.
        --}}

        {{-- Gunakan parameter reportId jika sedang mengedit laporan yang ada --}}
        @isset($reportId)
            @livewire('laporan.harian', ['reportId' => $reportId])
        @else
            @livewire('laporan.harian')
        @endisset
    </div>
@endsection