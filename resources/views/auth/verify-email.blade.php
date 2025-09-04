@extends('layouts.app')

@section('title', 'Verifikasi Email Anda')

@section('content')
<div class="space-y-6 text-center">

    <div>
        <h1 class="text-3xl font-bold text-gray-900">Satu Langkah Lagi!</h1>
        <p class="mt-2 text-base text-gray-600">
            Terima kasih telah mendaftar. Sebelum melanjutkan, silakan periksa email Anda untuk link verifikasi.
        </p>
    </div>

    @if (session('message'))
        <div class="bg-green-100 text-green-800 text-sm font-medium p-4 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 text-center">
        <p class="text-sm text-gray-700">
            Jika Anda tidak menerima email, kami akan dengan senang hati mengirimkannya lagi.
        </p>

        <form class="mt-4" action="{{ route('verification.send') }}" method="POST">
            @csrf
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 active:scale-95 transition-transform">
                Kirim Ulang Email Verifikasi
            </button>
        </form>
    </div>

    <div class="flex items-center justify-center space-x-4">
        <a href="{{ route('client.profil.index') }}" class="text-sm font-medium text-gray-600 hover:underline">
            Ke Halaman Profil
        </a>
        <span class="text-gray-300">|</span>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm font-medium text-gray-600 hover:underline">
                Logout
            </button>
        </form>
    </div>
</div>
@endsection
