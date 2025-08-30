@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto">
    @include('auth.register.partials.steps', ['active' => 2])


    <h1 class="text-2xl font-semibold mb-4">Domisili & Kontak</h1>

    <form action="{{ route('register.step2.post') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Tanggal Lahir (konfirmasi)</label>
            <input type="text" value="{{ session('register.tanggal_lahir') }}" readonly
                   class="input-modern">
            <small class="text-gray-500">Sudah diisi pada Langkah 1</small>
        </div>
        <div>
            <label class="block text-sm font-medium">Tempat Tinggal (Provinsi)</label>
            <select name="alamat" required
                    class="input-modern">
                <option value="">— Pilih Provinsi —</option>
                @foreach($provinces as $prov)
                    <option value="{{ $prov }}" {{ old('alamat', session('register.alamat')) === $prov ? 'selected' : '' }}>{{ $prov }}</option>
                @endforeach
            </select>
            @error('alamat')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Pekerjaan</label>
            <select name="pekerjaan"
                    class="input-modern">
                <option value="">— Pilih Pekerjaan —</option>
                @foreach($jobs as $job)
                    <option value="{{ $job }}" {{ old('pekerjaan', session('register.pekerjaan')) === $job ? 'selected' : '' }}>{{ $job }}</option>
                @endforeach
            </select>
            @error('pekerjaan')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Nomor Telepon</label>
            <input
                type="tel"
                name="nomor_telepon"
                value="{{ old('nomor_telepon', session('register.nomor_telepon')) }}"
                required
                inputmode="tel"
                autocomplete="tel"
                maxlength="16"
                placeholder="+62 812-3456-7890"
                pattern="^\+628\d{8,11}$"
                class="input-modern"
            >
            <p class="text-xs text-gray-500 mt-1">
                Format: <strong>+62</strong> diikuti nomor seluler (contoh: <code>+62 812-3456-7890</code>).
                Kamu boleh ketik <code>08...</code>, nanti otomatis diubah ke <code>+62...</code>.
            </p>
            @error('nomor_telepon')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between">
            <a href="{{ route('register') }}" class="px-4 py-2 rounded-md border">Kembali</a>
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white">Lanjut</button>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tel = document.querySelector('input[name="nomor_telepon"]');
    if (!tel) return;
    const form = tel.closest('form');

    function normalize(v){
        if(!v) return v;
        v = v.replace(/[\s\-.]/g,'');     // hapus spasi/dash/titik
        if (v.startsWith('+62')) return v;
        if (v.startsWith('0'))   return '+62' + v.slice(1);
        if (v.startsWith('62'))  return '+' + v;
        return v;
    }

    tel.addEventListener('blur',  () => { tel.value = normalize(tel.value); });
    if (form) form.addEventListener('submit', () => { tel.value = normalize(tel.value); });
});
</script>
@endsection