@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<div class="space-y-6 mx-auto max-w-md">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Lupa Password</h1>
        <p class="mt-2 text-base text-gray-600">
            Fitur reset password belum tersedia. Silakan hubungi administrator atau dukungan
            untuk mendapatkan bantuan mengganti password Anda.
        </p>
    </div>
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-green-600 hover:underline">Kembali ke halaman masuk</a>
    </div>
</div>
@endsection