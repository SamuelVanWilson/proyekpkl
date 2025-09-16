@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="space-y-6 mx-auto max-w-md">
    {{-- Header --}}
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Reset Password</h1>
        <p class="mt-2 text-base text-gray-600">
            Masukkan alamat email dan password baru Anda.
        </p>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 text-sm font-medium p-4 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Reset Password Form --}}
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="label-modern">Alamat Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input-modern mt-1" placeholder="contoh@email.com" autofocus>
        </div>
        <div>
            <label for="password" class="label-modern">Password Baru</label>
            <input id="password" type="password" name="password" required class="input-modern mt-1" placeholder="Minimal 8 karakter">
        </div>
        <div>
            <label for="password_confirmation" class="label-modern">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="input-modern mt-1" placeholder="Ulangi password baru">
        </div>
        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-3 px-4 rounded-lg font-semibold text-white bg-green-600 hover:bg-green-700 active:scale-95 transition-transform">
                Reset Password
            </button>
        </div>
    </form>

    {{-- Back to login --}}
    <p class="text-center text-sm text-gray-600">
        <a href="{{ route('login') }}" class="font-medium text-green-600 hover:underline">Kembali ke halaman masuk</a>
    </p>
</div>
@endsection