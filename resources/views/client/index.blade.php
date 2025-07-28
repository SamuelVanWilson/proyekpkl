@extends('layouts.client')

@section('title', 'Laporan Saya')

@section('content')

{{-- Header Halaman --}}
<div class="bg-white pt-10 pb-6 px-4 safe-area-top border-b border-gray-200 sticky top-0">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">
            Laporan
        </h1>
        <button class="text-gray-500 hover:text-gray-900">
            <ion-icon name="search-outline" class="text-2xl"></ion-icon>
        </button>
    </div>
</div>

{{-- Konten Daftar Laporan --}}
<div class="p-4 space-y-4">

    {{-- Pesan Sukses --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-800 text-sm font-medium p-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Loop untuk setiap laporan --}}
    @forelse ($reports as $report)
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $report->lokasi }}</p>
                    <p class="text-xs text-gray-500">{{ $report->tanggal->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
                {{-- Tombol Aksi (Edit, Hapus, dll) --}}
                <div class="text-xs text-gray-400">
                    ID: #{{ $report->id }}
                </div>
            </div>
            <div class="mt-4 border-t border-gray-100 pt-3">
                <p class="text-xs text-gray-500">Total Uang</p>
                <p class="text-xl font-bold text-gray-900">Rp {{ number_format($report->total_uang, 0, ',', '.') }}</p>
            </div>
            <div class="mt-4 flex space-x-2">
                <a href="{{-- route('client.laporan.edit', $report) --}}" class="flex-1 text-center bg-gray-100 text-gray-800 py-2 rounded-lg text-sm font-medium hover:bg-gray-200">
                    Edit
                </a>
                <a href="{{ route('client.laporan.pdf.preview', $report) }}" target="_blank" class="flex-1 text-center bg-blue-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-600">
                    Lihat PDF
                </a>
            </div>
        </div>
    @empty
        {{-- Tampilan jika tidak ada laporan --}}
        <div class="text-center py-16 px-4">
            <ion-icon name="document-text-outline" class="text-5xl text-gray-300 mx-auto"></ion-icon>
            <h3 class="mt-2 text-lg font-medium text-gray-800">Belum Ada Laporan</h3>
            <p class="mt-1 text-sm text-gray-500">
                Tekan tombol '+' untuk membuat laporan pertama Anda.
            </p>
        </div>
    @endforelse

    {{-- Link Paginasi --}}
    <div class="py-4">
        {{ $reports->links() }}
    </div>
</div>

{{-- Tombol Tambah Mengambang (Floating Action Button) --}}
<a href="{{-- route('client.laporan.create') --}}" class="fixed bottom-20 right-5 h-14 w-14 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-lg hover:bg-blue-600 transition-transform active:scale-90">
    <ion-icon name="add-outline" class="text-3xl"></ion-icon>
</a>
@endsection
