@extends('layouts.client')

@section('title', 'Konfigurasi Detail Laporan')

@section('content')
<div class="p-4 sm:p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Atur Detail Laporan</h1>
            <p class="text-gray-600 mt-1">Sesuaikan kolom pada detail laporan Anda.</p>
        </div>

        {{-- Tampilkan pesan sukses jika ada --}}
        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('client.laporan.simple.config.update', $report->id) }}" x-ref="form">
            @csrf
            @method('PUT')

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-sm border border-gray-200">
                <p class="text-sm text-gray-600 mb-4">Kolom bawaan <strong>Judul Laporan</strong> dan <strong>Tanggal Laporan</strong> akan selalu ada dan tidak bisa dihapus.</p>
                <div id="fields" class="space-y-4">
                    @foreach($schema as $i => $f)
                        @if(in_array($f['key'], ['title','tanggal_raw']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center p-3 bg-gray-50 rounded-lg border">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Label</label>
                                    <input type="text" class="input-modern bg-gray-100" value="{{ $f['label'] }}" disabled>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tipe</label>
                                    <input type="text" class="input-modern bg-gray-100" value="{{ $f['type'] }}" disabled>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center p-3 bg-gray-50 rounded-lg border">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Label</label>
                                    <input name="fields[{{ $i }}][label]" value="{{ $f['label'] }}" class="input-modern" required>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tipe</label>
                                    <select name="fields[{{ $i }}][type]" class="input-modern">
                                        <option value="text" {{ $f['type']=='text'?'selected':'' }}>Teks</option>
                                        <option value="number" {{ $f['type']=='number'?'selected':'' }}>Angka</option>
                                        <option value="date" {{ $f['type']=='date'?'selected':'' }}>Tanggal</option>
                                    </select>
                                </div>
                                <div class="flex justify-end">
                                    <button type="button" class="px-3 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md" onclick="this.closest('.grid').remove();">Hapus</button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4">
                    <button type="button" id="addField" class="w-full sm:w-auto px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium">+ Tambah Kolom</button>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                {{-- Tombol kembali menggunakan history.back agar kembali ke halaman laporan --}}
                <a href="#" onclick="event.preventDefault(); history.back();" class="w-full sm:w-auto text-center px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Kembali
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 flex items-center justify-center">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrap = document.getElementById('fields');
        const addFieldBtn = document.getElementById('addField');
        if (addFieldBtn) {
            addFieldBtn.addEventListener('click', function(){
                const idx = wrap.querySelectorAll('.grid').length;
                const el  = document.createElement('div');
                el.className = 'grid grid-cols-1 sm:grid-cols-3 gap-3 items-center p-3 bg-gray-50 rounded-lg border';
                el.innerHTML = `
                    <div>
                        <label class="text-sm font-medium text-gray-500">Label</label>
                        <input name="fields[${idx}][label]" class="input-modern" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Tipe</label>
                        <select name="fields[${idx}][type]" class="input-modern">
                            <option value="text">Teks</option>
                            <option value="number">Angka</option>
                            <option value="date">Tanggal</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="px-3 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md" onclick="this.closest('.grid').remove();">Hapus</button>
                    </div>
                `;
                wrap.appendChild(el);
            });
        }
    });
</script>
@endsection
