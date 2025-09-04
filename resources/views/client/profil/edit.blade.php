@extends('layouts.client')

@section('title', 'Ubah Profil')

@section('content')
<div class="relative">
    {{-- Latar belakang blur untuk memberikan efek fokus pada form --}}
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-0"></div>
    <div class="relative z-10 p-4 flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl md:max-w-3xl p-6">
            <h1 class="text-2xl font-semibold mb-4 text-center">Ubah Data Diri</h1>
            <form method="POST" action="{{ route('client.profil.update') }}" class="space-y-4">
                @csrf
                {{-- Gunakan grid dengan dua kolom untuk layar medium ke atas agar form ditata horizontal --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm" required>
                        @error('name') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm" required>
                        @error('email') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                        @error('tanggal_lahir') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <input type="text" name="alamat" value="{{ old('alamat', $user->alamat) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                        @error('alamat') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $user->pekerjaan) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                        @error('pekerjaan') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm">
                        @error('nomor_telepon') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (opsional)</label>
                        <input type="password" name="password" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm" placeholder="Minimal 8 karakter">
                        @error('password') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-lg py-2 px-3 text-sm" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="flex justify-between gap-2 pt-4">
                    <a href="{{ route('client.profil.index') }}" class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-lg text-sm font-medium">Batal</a>
                    <button type="submit" class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection