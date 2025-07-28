@extends('layouts.app')

@section('content')
<div class="space-y-8">
    {{-- Bagian Header --}}
    <div class="text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-500 text-white shadow-lg">
            {{-- Menggunakan ikon dari Heroicons --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h1 class="mt-5 text-2xl font-bold text-gray-900">
            Selamat Datang
        </h1>
        <p class="mt-2 text-base text-gray-600">
            Masuk untuk mengelola laporan Anda.
        </p>
    </div>

    {{-- Form Login dengan perbaikan routing --}}
    <div class="bg-white p-4 rounded-xl shadow-sm">
        <form action="{{ route('login.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="kode_unik" class="text-sm font-medium text-gray-500 sr-only">
                    Kode Unik
                </label>
                <div class="mt-1">
                    <input
                        type="text"
                        name="kode_unik"
                        id="kode_unik"
                        class="block w-full px-4 py-3.5 bg-gray-100 border-gray-200 rounded-lg text-center text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-lg tracking-wider"
                        placeholder="MASUKKAN KODE UNIK"
                        required
                        autofocus>
                </div>
            </div>

            {{-- Menampilkan Pesan Error --}}
            @if ($errors->any())
                <div class="bg-red-50 text-red-700 text-sm p-3 rounded-lg">
                    <p>{{ $errors->first('kode_unik') }}</p>
                </div>
            @endif

            <div>
                <button
                    type="submit"
                    class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-transform active:scale-95">
                    Masuk
                </button>
            </div>
        </form>
    </div>

    {{-- Footer --}}
    <p class="mt-8 text-center text-xs text-gray-400">
        Versi 1.0.0 &copy; {{ date('Y') }}
    </p>
</div>
@endsection
