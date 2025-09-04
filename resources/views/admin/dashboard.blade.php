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
                <li>
                    <a href="{{ route('admin.users.activity', $report->user) }}" class="block hover:bg-gray-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-green-600 truncate">{{ $report->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $report->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            Membuat laporan baru di <span class="font-semibold">{{ $report->lokasi }}</span>
                        </div>
                    </a>
                </li>
            @empty
                <li class="p-6 text-sm text-gray-500 text-center">Belum ada aktivitas.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
