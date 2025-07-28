@extends('layouts.admin')

@section('title', 'Aktivitas Klien: ' . $user->name)

@section('content')
<div>
    <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke daftar klien</a>
    <h1 class="text-2xl font-semibold text-gray-900 mt-2">{{ $user->name }}</h1>
    <p class="mt-1 text-sm text-gray-500">Menampilkan riwayat aktivitas terbaru.</p>
</div>

<div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
    <!-- Recent Reports -->
    <div>
        <h2 class="text-lg font-medium text-gray-900">Laporan Dibuat</h2>
        <ul class="mt-4 space-y-3">
            @forelse($user->dailyReports as $report)
            <li class="bg-white shadow overflow-hidden rounded-md px-6 py-4">
                <p class="font-medium text-gray-900">{{ $report->lokasi }}</p>
                <p class="text-sm text-gray-500">{{ $report->tanggal->format('d M Y, H:i') }} - Rp {{ number_format($report->total_uang) }}</p>
            </li>
            @empty
            <li class="text-sm text-gray-500">Tidak ada laporan yang dibuat.</li>
            @endforelse
        </ul>
    </div>

    <!-- Recent PDF Exports -->
    <div>
        <h2 class="text-lg font-medium text-gray-900">Ekspor PDF</h2>
        <ul class="mt-4 space-y-3">
            @forelse($user->pdfExports as $export)
            <li class="bg-white shadow overflow-hidden rounded-md px-6 py-4">
                <p class="font-medium text-gray-900">{{ $export->filename }}</p>
                <p class="text-sm text-gray-500">Diekspor pada {{ $export->created_at->format('d M Y, H:i') }}</p>
            </li>
            @empty
            <li class="text-sm text-gray-500">Tidak ada aktivitas ekspor PDF.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
