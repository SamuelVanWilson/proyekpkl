@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<div class="space-y-6 mx-auto max-w-md">
    {{-- Header --}}
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Lupa Password</h1>
        <p class="mt-2 text-base text-gray-600">
            Masukkan alamat email Anda dan kami akan mengirimkan link untuk mengatur ulang password.
        </p>
    </div>

    {{-- Success Message --}}
    @if (session('status'))
        <div class="bg-green-100 text-green-800 text-sm font-medium p-4 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    {{-- Form Reset Password --}}
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="label-modern">Alamat Email</label>
            <input id="email" type="email" name="email" required class="input-modern mt-1" placeholder="contoh@email.com" value="{{ old('email') }}">
            @error('email')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-lg font-semibold text-white bg-green-600 hover:bg-green-700 active:scale-95 transition-transform">
                Kirim Link Reset
            </button>
        </div>
    </form>

    {{-- Back to login --}}
    <p class="text-center text-sm text-gray-600">
        <a href="{{ route('login') }}" class="font-medium text-green-600 hover:underline">Kembali ke halaman masuk</a>
    </p>
</div>
@endsection