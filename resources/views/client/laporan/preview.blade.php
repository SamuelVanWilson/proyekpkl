@extends('layouts.client')

@section('title', 'Preview Laporan')

@section('content')
<div class="container mx-auto p-4" x-data="{ order: ['pdf','meta'], dragging: null }">
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
        <template x-for="section in order" :key="section">
            <div x-bind:class="section === 'pdf' ? 'flex-grow lg:w-2/3 border border-gray-200 rounded-lg overflow-x-auto' : 'lg:w-1/3 bg-white border border-gray-200 rounded-lg p-4 flex flex-col gap-4'"
                 draggable="true"
                 @dragstart="dragging = section"
                 @dragover.prevent
                 @drop="if(dragging && dragging !== section){ let idxFrom = order.indexOf(dragging); let idxTo = order.indexOf(section); order.splice(idxFrom,1); order.splice(idxTo,0, dragging); dragging=null;}"
            >
                <template x-if="section === 'pdf'">
                    <iframe src="{{ route('client.laporan.histori.pdf', $report) }}" class="w-full h-[70vh] min-w-[600px]" frameborder="0"></iframe>
                </template>
                <template x-if="section === 'meta'">
                    <div class="flex flex-col h-full">
                        <form action="{{ route('client.laporan.preview.update', $report) }}" method="POST" enctype="multipart/form-data" class="space-y-4 flex flex-col flex-grow">
                            @csrf
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Judul Laporan</label>
                                <input type="text" name="title" id="title" value="{{ $report->data['meta']['title'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Logo Perusahaan</label>
                                @if(!empty($report->data['meta']['logo']))
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($report->data['meta']['logo']) }}" alt="Logo" class="h-16 object-contain border rounded">
                                    </div>
                                @endif
                                <input type="file" name="logo" class="block w-full text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-md cursor-pointer focus:outline-none">
                                <p class="text-xs text-gray-500 mt-1">Unggah logo baru (opsional)</p>
                            </div>
                            <div>
                                <label for="header_row" class="block text-sm font-medium text-gray-700">Baris Judul Kolom</label>
                                <select name="header_row" id="header_row" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                                    {{-- Pilihan baris judul kolom: 1 hingga jumlah baris yang ada --}}
                                    @php
                                        $rowCount = isset($report->data['rows']) ? count($report->data['rows']) : 0;
                                        $selectedHeader = $report->data['meta']['header_row'] ?? 1;
                                    @endphp
                                    @for($i = 1; $i <= max($rowCount, 1); $i++)
                                        <option value="{{ $i }}" {{ $selectedHeader == $i ? 'selected' : '' }}>Baris {{ $i }}</option>
                                    @endfor
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Pilih baris mana yang akan dijadikan sebagai judul kolom di PDF.</p>
                            </div>
                            <div>
                                <label for="detail_pos" class="block text-sm font-medium text-gray-700">Posisi Detail Laporan</label>
                                @php
                                    $detailPos = $report->data['meta']['detail_pos'] ?? 'top';
                                @endphp
                                <select name="detail_pos" id="detail_pos" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="top" {{ $detailPos === 'top' ? 'selected' : '' }}>Di Atas Tabel</option>
                                    <option value="bottom" {{ $detailPos === 'bottom' ? 'selected' : '' }}>Di Bawah Tabel</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Tentukan apakah detail laporan tampil di atas atau di bawah tabel data di PDF.</p>
                            </div>
                            <div class="mt-auto flex gap-2">
                                <a href="{{ route('client.laporan.edit', $report) }}" class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg text-sm font-medium">Kembali</a>
                                <button type="submit" class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium">Simpan Meta</button>
                            </div>
                        </form>
                        <div class="mt-4">
                            <a href="{{ route('client.laporan.histori.download', $report) }}" class="w-full block text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium">Unduh PDF</a>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
@endsection