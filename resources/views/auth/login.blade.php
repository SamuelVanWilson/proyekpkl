@extends('layouts.app')

@section('title', 'Masuk ke Akun Anda')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Selamat Datang Kembali</h1>
        <p class="mt-2 text-base text-gray-600">Masukkan detail akun Anda untuk melanjutkan.</p>
    </div>

    {{-- Form Login Baru --}}
    <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Input Email --}}
        <div>
            <label for="email" class="label-modern">Alamat Email</label>
            <input id="email" name="email" type="email" required class="input-modern mt-1" value="{{ old('email') }}" placeholder="contoh@email.com" autofocus>
            @error('email') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Input Password --}}
        <div>
            <label for="password" class="label-modern">Password</label>
            <input id="password" name="password" type="password" required class="input-modern mt-1" placeholder="Masukkan password Anda">
            @error('password') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>

        {{-- Tombol Submit --}}
        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 active:scale-95 transition-transform">
                Masuk
            </button>
        </div>
    </form>

    {{-- Link ke Halaman Registrasi --}}
    <p class="text-center text-sm text-gray-600">
        Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:underline">Daftar sekarang</a>
    </p>
</div>
@endsection
