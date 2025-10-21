@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto">
    @include('auth.register.partials.steps', ['active' => 1])


    <h1 class="text-2xl font-semibold mb-4">Registrasi — Data Diri</h1>

    <form action="{{ route('register.step1.post') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', session('register.name')) }}" required
                   class="input-modern">
            @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', session('register.tanggal_lahir')) }}" required
                   class="input-modern">
            @error('tanggal_lahir')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between">
            <a href="{{ route('login') }}" class="px-4 py-2 rounded-md border">Login</a>
            <button class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">Lanjut</button>
        </div>
      </form>
</div>
@endsection
