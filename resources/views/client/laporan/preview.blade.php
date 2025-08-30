@extends('layouts.client')

@section('title', 'Preview Laporan')

@section('content')
<div class="container mx-auto p-4">
    {{-- Header --}}
    <div class="mb-4">
        <h1 class="text-2xl font-semibold">Preview Laporan</h1>
    </div>
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 p-3 rounded mb-4 text-sm">
            {{ session('success') }}
        </div>
    @endif
    {{-- Layout responsive: stack on mobile, side‑by‑side on large screens --}}
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- PDF preview section --}}
        <div class="flex-grow lg:w-2/3 border border-gray-200 rounded-lg overflow-hidden">
            <iframe src="{{ route('client.laporan.histori.pdf', $report) }}" class="w-full h-[70vh]" frameborder="0"></iframe>
        </div>
        {{-- Sidebar for editing meta and export --}}
        <div class="lg:w-1/3 bg-white border border-gray-200 rounded-lg p-4 flex flex-col gap-4">
            <form action="{{ route('client.laporan.preview.update', $report) }}" method="POST" enctype="multipart/form-data" class="space-y-4 flex flex-col flex-grow">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Judul Laporan</label>
                    <input type="text" name="title" id="title" value="{{ $report->data['meta']['title'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Logo Perusahaan</label>
                    @if(!empty($report->data['meta']['logo']))
                        <div class="mb-2">
                            <img src="{{ asset('storage/'.$report->data['meta']['logo']) }}" alt="Logo" class="h-16 object-contain border rounded">
                        </div>
                    @endif
                    <input type="file" name="logo" class="block w-full text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-md cursor-pointer focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Unggah logo baru (opsional)</p>
                </div>
                <div class="mt-auto flex gap-2">
                    <a href="{{ route('client.laporan.edit', $report) }}" class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg text-sm font-medium">Kembali</a>
                    <button type="submit" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium">Simpan Meta</button>
                </div>
            </form>
            <div>
                <a href="{{ route('client.laporan.histori.download', $report) }}" class="w-full block text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium">Unduh PDF</a>
            </div>
        </div>
    </div>
</div>
@endsection