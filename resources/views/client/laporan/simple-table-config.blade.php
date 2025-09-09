@extends('layouts.client')

@section('title', 'Konfigurasi Laporan Biasa')

@section('content')
    <div class="container mx-auto p-4 max-w-xl">
        <h1 class="text-2xl font-semibold mb-4">Konfigurasi Tabel Laporan Biasa</h1>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.laporan.simple.config.update', $report) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="column_count" class="block text-sm font-medium text-gray-700">Jumlah Kolom (1–26)</label>
                <input type="number" id="column_count" name="column_count" value="{{ old('column_count', $columnCount) }}" min="1" max="26"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" />
                @error('column_count')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2">
                <a href="{{ route('client.laporan.edit', $report) }}" class="flex-1 text-center py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">Kembali</a>
                <button type="submit" class="flex-1 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white">Simpan</button>
            </div>
        </form>
        <p class="text-xs text-gray-500 mt-2">*Konfigurasi hanya berlaku untuk laporan ini dan tidak memengaruhi laporan lainnya.</p>
    </div>
@endsection