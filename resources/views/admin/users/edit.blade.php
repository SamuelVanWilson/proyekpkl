@extends('layouts.admin')

@section('title', 'Edit Klien: ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-4">Edit Klien</h1>
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-lg border @error('name') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-lg border @error('email') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password Baru (Opsional)</label>
                <input id="password" type="password" name="password" class="mt-1 block w-full rounded-lg border @error('password') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm" placeholder="Minimal 8 karakter">
                @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="mt-1 block w-full rounded-lg border border-gray-300 py-2 px-3 text-sm" placeholder="Ulangi password baru">
            </div>
            <div>
                <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                <input id="alamat" type="text" name="alamat" value="{{ old('alamat', $user->alamat) }}" class="mt-1 block w-full rounded-lg border @error('alamat') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('alamat')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" class="mt-1 block w-full rounded-lg border @error('tanggal_lahir') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('tanggal_lahir')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="pekerjaan" class="block text-sm font-medium text-gray-700">Pekerjaan</label>
                <input id="pekerjaan" type="text" name="pekerjaan" value="{{ old('pekerjaan', $user->pekerjaan) }}" class="mt-1 block w-full rounded-lg border @error('pekerjaan') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('pekerjaan')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="nomor_telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                <input id="nomor_telepon" type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" class="mt-1 block w-full rounded-lg border @error('nomor_telepon') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('nomor_telepon')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role" name="role" class="mt-1 block w-full rounded-lg border @error('role') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                <label for="is_active" class="ml-2 text-sm text-gray-700">Akun Aktif</label>
                @error('is_active')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="subscription_plan" class="block text-sm font-medium text-gray-700">Paket Langganan</label>
                <select name="subscription_plan" id="subscription_plan" class="mt-1 block w-full rounded-lg border @error('subscription_plan') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                    <option value="" {{ old('subscription_plan', $user->subscription_plan) === null ? 'selected' : '' }}>— Tidak Ada —</option>
                    <option value="mingguan" {{ old('subscription_plan', $user->subscription_plan) === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ old('subscription_plan', $user->subscription_plan) === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="3_bulan" {{ old('subscription_plan', $user->subscription_plan) === '3_bulan' ? 'selected' : '' }}>3 Bulan</option>
                </select>
                @error('subscription_plan')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="subscription_expires_at" class="block text-sm font-medium text-gray-700">Kadaluarsa Langganan</label>
                <input type="datetime-local" name="subscription_expires_at" id="subscription_expires_at" value="{{ old('subscription_expires_at', optional($user->subscription_expires_at)->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-lg border @error('subscription_expires_at') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('subscription_expires_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="offer_expires_at" class="block text-sm font-medium text-gray-700">Kadaluarsa Penawaran</label>
                <input type="datetime-local" name="offer_expires_at" id="offer_expires_at" value="{{ old('offer_expires_at', optional($user->offer_expires_at)->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-lg border @error('offer_expires_at') border-red-500 @else border-gray-300 @enderror py-2 px-3 text-sm">
                @error('offer_expires_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border text-gray-700">Batal</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection