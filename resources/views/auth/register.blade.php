@extends('layouts.app')

@section('title', 'Buat Akun')

@php
    // Data untuk dropdown, diurutkan secara alfabetis
    $provinsi = [
        'Aceh', 'Bali', 'Banten', 'Bengkulu', 'Gorontalo', 'DKI Jakarta', 'Jambi', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'Kalimantan Barat', 'Kalimantan Selatan', 'Kalimantan Tengah', 'Kalimantan Timur', 'Kalimantan Utara', 'Kepulauan Bangka Belitung', 'Kepulauan Riau', 'Lampung', 'Maluku', 'Maluku Utara', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Papua', 'Papua Barat', 'Riau', 'Sulawesi Barat', 'Sulawesi Selatan', 'Sulawesi Tengah', 'Sulawesi Tenggara', 'Sulawesi Utara', 'Sumatera Barat', 'Sumatera Selatan', 'Sumatera Utara', 'DI Yogyakarta'
    ];
    sort($provinsi);

    $pekerjaan = [
        'Pelajar/Mahasiswa', 'Karyawan Swasta', 'Wiraswasta', 'Pegawai Negeri Sipil (PNS)', 'TNI/Polri', 'Profesional (Dokter, Pengacara, dll)', 'Ibu Rumah Tangga', 'Lainnya'
    ];
@endphp

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Buat Akun Baru</h1>
        <p class="mt-2 text-base text-gray-600">Mulai kelola laporan Anda dalam sekejap.</p>
    </div>

    <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Nama Lengkap --}}
        <div>
            <label for="name" class="label-modern">Nama Lengkap</label>
            <input id="name" name="name" type="text" required class="input-modern mt-1" value="{{ old('name') }}" placeholder="Masukkan nama Anda">
            @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="label-modern">Alamat Email</label>
            <input id="email" name="email" type="email" required class="input-modern mt-1" value="{{ old('email') }}" placeholder="contoh@email.com">
            @error('email') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Tanggal Lahir --}}
        <div>
            <label for="tanggal_lahir" class="label-modern">Tanggal Lahir</label>
            <input id="tanggal_lahir" name="tanggal_lahir" type="date" required class="input-modern mt-1" value="{{ old('tanggal_lahir') }}">
            @error('tanggal_lahir') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Tanggal Lahir --}}
        <div>
            <label for="nomor_telepon" class="label-modern">Tanggal Lahir</label>
            <input id="nomor_telepon" name="nomor_telepon" type="text" required class="input-modern mt-1" value="{{ old('nomor_telepon') }}">
            @error('nomor_telepon') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Alamat (Provinsi) --}}
        <div>
            <label for="alamat" class="label-modern">Provinsi</label>
            <select id="alamat" name="alamat" required class="input-modern mt-1">
                <option value="" disabled selected>Pilih Provinsi</option>
                @foreach($provinsi as $p)
                    <option value="{{ $p }}" @if(old('alamat') == $p) selected @endif>{{ $p }}</option>
                @endforeach
            </select>
            @error('alamat') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Pekerjaan --}}
        <div>
            <label for="pekerjaan" class="label-modern">Pekerjaan</label>
            <select id="pekerjaan" name="pekerjaan" required class="input-modern mt-1">
                <option value="" disabled selected>Pilih Pekerjaan</option>
                 @foreach($pekerjaan as $p)
                    <option value="{{ $p }}" @if(old('pekerjaan') == $p) selected @endif>{{ $p }}</option>
                @endforeach
            </select>
            @error('pekerjaan') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="label-modern">Password</label>
            <input id="password" name="password" type="password" required class="input-modern mt-1" placeholder="Minimal 8 karakter">
            @error('password') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Konfirmasi Password --}}
        <div>
            <label for="password_confirmation" class="label-modern">Konfirmasi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="input-modern mt-1" placeholder="Ketik ulang password Anda">
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-lg font-semibold text-white bg-green-600 hover:bg-green-700 active:scale-95 transition-transform">
                Daftar
            </button>
        </div>
    </form>
    <p class="text-center text-sm text-gray-600">
        Sudah punya akun? <a href="{{ route('login') }}" class="font-medium text-green-600 hover:underline">Masuk di sini</a>
    </p>
</div>
@endsection
