@extends('layouts.client')

@section('title', 'Laporan Saya')

@section('content')

{{-- Header Halaman --}}
<div class="bg-white pt-10 pb-6 px-4 safe-area-top border-b border-gray-200 sticky top-0">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">
            Laporan
        </h1>
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
                    {{-- Judul laporan: gunakan judul meta dari data jika ada, jika tidak isi default. --}}
                    @if(!empty($report->data))
                        @php
                            // Ambil judul dari meta, fallback "Laporan Tidak Diketahui" jika kosong
                            $judul = $report->data['meta']['title'] ?? '';
                            $judul = trim($judul) !== '' ? $judul : 'Laporan Tidak Diketahui';
                        @endphp
                        <p class="text-sm font-semibold text-gray-800">{{ $judul }}</p>
                    @else
                        <p class="text-sm font-semibold text-gray-800">{{ $report->lokasi }}</p>
                    @endif
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($report->tanggal)->isoFormat('dddd, D MMMM Y') }}</p>
                </div>
                <div class="text-xs text-gray-400">
                    ID: #{{ $report->id }}
                </div>
            </div>
            {{-- Tombol Aksi: hanya Edit dan Hapus. Preview & unduh kini tersedia dari halaman laporan. --}}
            <div class="mt-4 flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                {{-- Tombol edit hanya untuk laporan biasa (data != null) --}}
                @if(!empty($report->data))
                    <a href="{{ route('client.laporan.edit', $report) }}" class="flex-1 text-center bg-gray-200 text-gray-800 py-2 rounded-lg text-sm font-medium hover:bg-gray-300">
                        Edit
                    </a>
                @endif
                {{-- Tombol hapus --}}
                <form method="POST" action="{{ route('client.laporan.destroy', $report) }}" class="flex-1" onsubmit="return confirm('Hapus laporan ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full text-center bg-red-500 text-white py-2 rounded-lg text-sm font-medium hover:bg-red-600">
                        Hapus
                    </button>
                </form>
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

{{-- Tombol Tambah dengan dropdown pilihan laporan. Laporan advanced dikunci bagi yang belum berlangganan --}}
<div x-data="{ open: false }" class="fixed bottom-20 right-5 z-50">
    <button @click="open = !open" class="h-14 w-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:bg-green-600 transition-transform active:scale-90 focus:outline-none">
        <ion-icon name="add-outline" class="text-3xl"></ion-icon>
    </button>
    <div x-show="open" @click.away="open = false" class="absolute bottom-16 right-0 mb-2 space-y-2">
        <a href="{{ route('client.laporan.harian') }}" class="block bg-white px-4 py-2 rounded-lg shadow text-sm font-medium text-gray-800 hover:bg-gray-100">
            Laporan Biasa
        </a>
        @if(auth()->user()->hasActiveSubscription())
            <a href="{{ route('client.laporan.advanced') }}" class="block bg-white px-4 py-2 rounded-lg shadow text-sm font-medium text-gray-800 hover:bg-gray-100">
                Laporan Advanced
            </a>
        @else
            <a href="{{ route('client.subscribe.show') }}" class="block bg-gray-200 px-4 py-2 rounded-lg shadow text-sm font-medium text-gray-400 cursor-pointer">
                Laporan Advanced
            </a>
        @endif
    </div>
</div>
@endsection

{{-- Sertakan AlpineJS untuk dropdown menu --}}
{{-- Muat AlpineJS agar dropdown berfungsi --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
