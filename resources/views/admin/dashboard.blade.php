@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
{{-- Header --}}
<div>
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
    <p class="mt-1 text-base text-gray-600">Selamat datang kembali, {{ Auth::user()->name }}.</p>
</div>

{{-- Statistik Utama dengan gaya iOS --}}
<div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-green-100 text-green-600">
                <ion-icon name="people-outline" class="text-xl"></ion-icon>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Klien</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalClients }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center bg-green-100 text-green-600">
                <ion-icon name="document-text-outline" class="text-xl"></ion-icon>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Laporan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalReports }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Aktivitas Terbaru --}}
<div class="mt-8">
    <h2 class="text-xl font-bold text-gray-900">Aktivitas Terbaru</h2>
    <div class="mt-4 bg-white shadow-sm overflow-hidden rounded-xl">
        <ul role="list" class="divide-y divide-gray-200">
            @forelse($recentReports as $report)
                @php
                    $meta  = $report->data['meta'] ?? [];
                    $rekap = $report->data['rekap'] ?? [];
                    $title = $meta['title'] ?? ($report->lokasi ?? 'Tanpa Judul');
                    $dateValue = $rekap['tanggal'] ?? $report->tanggal;
                    try {
                        $formattedDate = \Carbon\Carbon::parse($dateValue)->isoFormat('D MMM Y');
                    } catch (Exception $e) {
                        $formattedDate = $dateValue;
                    }
                    if (!empty($report->data['rincian']) || !empty($report->data['rekap'])) {
                        $type = 'Advanced';
                    } elseif (!empty($report->data['rows'])) {
                        $type = 'Biasa';
                    } else {
                        $type = 'Lama';
                    }
                @endphp
                <li class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-green-600 truncate">
                            <a href="{{ route('admin.users.activity', $report->user) }}" class="hover:underline">{{ $report->user->name }}</a>
                        </p>
                        <p class="text-xs text-gray-500">{{ $report->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        Laporan "<span class="font-semibold">{{ $title }}</span>" — {{ $formattedDate }} ({{ $type }})
                    </div>
                </li>
            @empty
                <li class="p-6 text-sm text-gray-500 text-center">Belum ada aktivitas.</li>
            @endforelse
        </ul>
    </div>
</div>

{{-- Insight Statistik Laporan --}}
<div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2">
    {{-- Top Klien Berdasarkan Laporan --}}
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <h3 class="text-base font-medium text-gray-900 mb-3">Top Klien Berdasarkan Jumlah Laporan</h3>
        <ul role="list" class="divide-y divide-gray-200">
            @forelse($reportCounts as $client)
                <li class="py-2 flex items-center justify-between">
                    <div class="flex items-center min-w-0">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <span class="text-sm font-semibold text-gray-600">{{ substr($client->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $client->name }}</p>
                        </div>
                    </div>
                    <span class="text-sm text-gray-500">{{ $client->daily_reports_count }} laporan</span>
                </li>
            @empty
                <li class="py-4 text-sm text-gray-500 text-center">Tidak ada data.</li>
            @endforelse
        </ul>
    </div>

    {{-- Statistik Jenis Laporan --}}
    <div class="bg-white p-5 rounded-xl shadow-sm">
        <h3 class="text-base font-medium text-gray-900 mb-3">Ragam Jenis Laporan</h3>
        <ul role="list" class="space-y-2">
            <li class="flex justify-between text-sm">
                <span>Advanced</span>
                <span class="font-semibold">{{ $reportTypeCounts['advanced'] }}</span>
            </li>
            <li class="flex justify-between text-sm">
                <span>Biasa</span>
                <span class="font-semibold">{{ $reportTypeCounts['biasa'] }}</span>
            </li>
            <li class="flex justify-between text-sm">
                <span>Lama</span>
                <span class="font-semibold">{{ $reportTypeCounts['lama'] }}</span>
            </li>
        </ul>
    </div>
</div>
@endsection
