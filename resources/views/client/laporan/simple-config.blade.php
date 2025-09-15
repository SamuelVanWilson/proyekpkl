@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <h1 class="text-xl font-semibold mb-4">Konfigurasi Tabel (Laporan Biasa)</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('client.laporan.simple.config.update', $report->id) }}">
        @csrf
        @method('PUT')

        <p class="text-sm text-gray-600 mb-3">Kolom bawaan <b>Judul Laporan</b> dan <b>Tanggal Laporan</b> akan selalu ada dan tidak bisa dihapus.</p>

        <div id="fields" class="space-y-3">
            @foreach($schema as $i => $f)
                @if(in_array($f['key'], ['title','tanggal_raw']))
                    <div class="border rounded p-3 bg-gray-50">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-600">Label</label>
                                <input type="text" class="w-full border rounded px-2 py-1 bg-gray-100" value="{{ $f['label'] }}" disabled>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Tipe</label>
                                <input type="text" class="w-full border rounded px-2 py-1 bg-gray-100" value="{{ $f['type'] }}" disabled>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="border rounded p-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-600">Label</label>
                                <input name="fields[{{ $i }}][label]" value="{{ $f['label'] }}" class="w-full border rounded px-2 py-1" required>
                            </div>
                            <div>
                                <label class="text-xs text-gray-600">Tipe</label>
                                <select name="fields[{{ $i }}][type]" class="w-full border rounded px-2 py-1">
                                    <option value="text" {{ $f['type']=='text'?'selected':'' }}>Teks</option>
                                    <option value="number" {{ $f['type']=='number'?'selected':'' }}>Angka</option>
                                    <option value="date" {{ $f['type']=='date'?'selected':'' }}>Tanggal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-4 flex gap-2">
            <button type="button" id="addField" class="px-3 py-2 rounded bg-gray-200">+ Tambah Kolom</button>
            <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white">Simpan</button>
            <a href="{{ route('client.laporan.edit', $report->id) }}" class="px-4 py-2 rounded border">Kembali</a>
        </div>
    </form>
</div>

<script>
document.getElementById('addField').addEventListener('click', function(){
    const wrap = document.getElementById('fields');
    const idx  = wrap.querySelectorAll('.border.rounded.p-3').length;
    const el   = document.createElement('div');
    el.className = 'border rounded p-3';
    el.innerHTML = `
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs text-gray-600">Label</label>
                <input name="fields[${idx}][label]" class="w-full border rounded px-2 py-1" required>
            </div>
            <div>
                <label class="text-xs text-gray-600">Tipe</label>
                <select name="fields[${idx}][type]" class="w-full border rounded px-2 py-1">
                    <option value="text">Teks</option>
                    <option value="number">Angka</option>
                    <option value="date">Tanggal</option>
                </select>
            </div>
        </div>`;
    wrap.appendChild(el);
});
</script>
@endsection
