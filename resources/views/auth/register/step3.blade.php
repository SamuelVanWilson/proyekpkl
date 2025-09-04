@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto">
    @include('auth.register.partials.steps', ['active' => 3])

    <h1 class="text-2xl font-semibold mb-4">Buat Akun</h1>

    <form action="{{ route('register.step3.post') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Alamat Email</label>
            <input type="email" name="email" value="{{ old('email', session('register.email')) }}" required
                   class="input-modern">
            @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Password</label>
            <input type="password" name="password" required
                   class="input-modern">
            @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required class="input-modern">
        </div>
        <div class="flex items-center justify-between">
            <a href="{{ route('register.step2.show') }}" class="px-4 py-2 rounded-md border">Kembali</a>
            <button class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">Lanjut</button>
        </div>
    </form>
</div>
@endsection