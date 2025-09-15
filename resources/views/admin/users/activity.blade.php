@extends('layouts.admin')

@section('title', 'Aktivitas Klien: ' . $user->name)

@section('content')
{{-- Header Halaman --}}
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-green-600 flex items-center mb-2">
        <ion-icon name="arrow-back-outline" class="mr-1"></ion-icon>
        Kembali ke Manajemen Klien
    </a>
    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
    <p class="mt-1 text-base text-gray-600">Menampilkan 10 riwayat aktivitas terbaru.</p>
</div>

{{-- Grid untuk menampung dua kolom aktivitas --}}
<div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
    <div>
        <h2 class="text-xl font-bold text-gray-900">Laporan Dibuat</h2>
        <div class="mt-4 bg-white shadow-sm overflow-hidden rounded-xl">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($user->dailyReports as $report)
                @php
                    // Ambil meta & rekap dari struktur data laporan
                    $meta  = $report->data['meta'] ?? [];
                    $rekap = $report->data['rekap'] ?? [];
                    // Judul laporan: gunakan judul meta jika ada, jika tidak gunakan lokasi atau fallback
                    $title = $meta['title'] ?? ($report->lokasi ?? 'Tanpa Judul');
                    // Tanggal laporan: ambil dari rekap jika ada, fallback ke kolom tanggal di tabel
                    $date  = $rekap['tanggal'] ?? $report->tanggal;
                    try {
                        $formattedDate = \Carbon\Carbon::parse($date)->isoFormat('D MMM Y');
                    } catch (Exception $e) {
                        $formattedDate = $date;
                    }
                    // Jenis laporan: tentukan berdasarkan keberadaan struktur data
                    if (!empty($report->data['rincian']) || !empty($report->data['rekap'])) {
                        $type = 'Advanced';
                    } elseif (!empty($report->data['rows'])) {
                        $type = 'Biasa';
                    } else {
                        $type = 'Lama';
                    }
                @endphp
                <li class="p-4">
                    <div class="flex justify-between items-start">
                        <div class="pr-4 flex-1">
                            <p class="font-semibold text-gray-800 truncate">{{ $title }}</p>
                            <p class="text-sm text-gray-500 mt-1">Tanggal: {{ $formattedDate }} | Jenis: {{ $type }}</p>
                        </div>
                        <span class="text-xs text-gray-500 ml-4">{{ $report->created_at->isoFormat('D MMM Y') }}</span>
                    </div>
                </li>
                @empty
                <li class="p-6 text-sm text-gray-500 text-center">
                    Klien ini belum membuat laporan.
                </li>
                @endforelse
            </ul>
        </div>
    </div>

    <div>
        <h2 class="text-xl font-bold text-gray-900">Ekspor PDF</h2>
        <div class="mt-4 bg-white shadow-sm overflow-hidden rounded-xl">
            <ul role="list" class="divide-y divide-gray-200">
                 @forelse($user->pdfExports as $export)
                    <li class="p-4">
                        <div class="flex items-center space-x-3">
                            <ion-icon name="document-outline" class="text-xl text-red-500"></ion-icon>
                            <div>
                                <p class="font-medium text-gray-800 truncate">{{ $export->filename }}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Diekspor pada {{ $export->created_at->isoFormat('D MMMM YYYY, HH:mm') }}
                                </p>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="p-6 text-sm text-gray-500 text-center">
                        Tidak ada aktivitas ekspor PDF.
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
