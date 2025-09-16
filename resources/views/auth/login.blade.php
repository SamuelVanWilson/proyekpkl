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
            <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-lg font-semibold text-white bg-green-600 hover:bg-green-700 active:scale-95 transition-transform">
                Masuk
            </button>
        </div>

    {{-- Lupa Password --}}
    <div class="text-center mt-2">
        <a href="{{ route('password.request') }}" class="text-sm text-green-600 hover:underline">Lupa password?</a>
    </div>
    </form>

    {{-- Divider --}}
    <div class="flex items-center mt-4">
        <hr class="flex-grow border-gray-300">
        <span class="mx-3 text-gray-400 text-sm">atau</span>
        <hr class="flex-grow border-gray-300">
    </div>

    {{-- Login dengan Google --}}
    <div class="pt-2">
        <a href="{{ route('auth.google') }}" class="w-full flex justify-center py-3 px-4 rounded-lg font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 active:scale-95 transition-transform">
            <svg class="h-5 w-5 mr-2" viewBox="0 0 48 48"><defs><path id="a" d="M44.5 20H24v8.5h11.8C34.5 33.6 29.8 36 24 36c-8.8 0-16-7.2-16-16s7.2-16 16-16c4.1 0 7.9 1.6 10.8 4.2l7.6-7.6C37.4 5.5 31 3 24 3 11.3 3 1 13.3 1 26s10.3 23 23 23 23-10.3 23-23c0-1.8-.2-3.5-.5-5z"/></defs><clipPath id="b"><use xlink:href="#a" overflow="visible"/></clipPath><path clip-path="url(#b)" fill="#FBBC05" d="M0 37V11l17 13z"/><path clip-path="url(#b)" fill="#EA4335" d="M0 11l17 13 7-6.1L48 14V0H0z"/><path clip-path="url(#b)" fill="#34A853" d="M0 37l30-23 7.6 2L48 0v48H0z"/><path clip-path="url(#b)" fill="#4285F4" d="M48 48L17 24l-4-3 35-10z"/></svg>
            Masuk dengan Google
        </a>
    </div>

    {{-- Link ke Halaman Registrasi --}}
    <p class="text-center text-sm text-gray-600">
        Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-green-600 hover:underline">Daftar sekarang</a>
    </p>
</div>
@endsection
