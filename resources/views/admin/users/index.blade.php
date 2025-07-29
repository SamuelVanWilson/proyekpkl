@extends('layouts.admin')

@section('title', 'Manajemen Klien')

@section('content')
{{-- Bagian Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Manajemen Klien</h1>
        <p class="mt-1 text-base text-gray-600">Daftar semua akun klien yang terdaftar.</p>
    </div>
    <div class="mt-4 shrink-0 sm:mt-0">
        <a href="{{ route('admin.users.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 transition-colors">
            <ion-icon name="add-outline" class="text-lg -ml-1 mr-2"></ion-icon>
            Tambah Klien
        </a>
    </div>
</div>

{{-- Pesan Sukses --}}
@if (session('success'))
    <div class="mt-6 bg-green-100 text-green-800 text-sm font-medium p-4 rounded-xl">
        {{ session('success') }}
    </div>
@endif

{{-- Daftar Klien Modern --}}
<div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 rounded-xl">
                <div class="min-w-full divide-y divide-gray-200">
                    {{-- Header Tabel (Hanya terlihat di desktop) --}}
                    <div class="hidden md:flex bg-gray-50">
                        <div class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider flex-1">Klien</div>
                        <div class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Status</div>
                        <div class="relative px-6 py-3 w-20">
                            <span class="sr-only">Aksi</span>
                        </div>
                    </div>

                    {{-- Body Tabel/Daftar --}}
                    <div class="bg-white divide-y divide-gray-200 pb-32">
                        @forelse ($users as $user)
                            <div class="flex flex-col md:flex-row md:items-center p-4 md:px-6 md:py-4">
                                {{-- Info Klien --}}
                                <div class="flex items-center flex-1 min-w-0">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-base font-semibold text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-4 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>

                                {{-- Status & Aksi (Dengan penyesuaian untuk mobile) --}}
                                <div class="flex items-center justify-between mt-4 md:mt-0">
                                    <div class="md:w-40 md:px-6">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>

                                    <div class="md:w-20 md:px-6 text-right">
                                        {{-- Dropdown Aksi --}}
                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button @click="open = !open" class="p-2 text-gray-500 rounded-full hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <span class="sr-only">Opsi</span>
                                                <ion-icon name="ellipsis-vertical" class="text-lg"></ion-icon>
                                            </button>

                                            <div x-show="open"
                                                 @click.away="open = false"
                                                 x-cloak
                                                 x-transition
                                                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                                <div class="py-1" role="menu" aria-orientation="vertical">
                                                    <a href="{{ route('admin.users.activity', $user) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Aktivitas</a>
                                                    <a href="{{ route('admin.users.edit', $user) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Klien</a>
                                                    <a href="{{ route('admin.users.form-builder', $user) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Form Builder</a>
                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus klien ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <ion-icon name="people-outline" class="mx-auto h-12 w-12 text-gray-400"></ion-icon>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada klien</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan klien baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pagination Links --}}
<div class="mt-6">
    {{ $users->links() }}
</div>
@endsection
