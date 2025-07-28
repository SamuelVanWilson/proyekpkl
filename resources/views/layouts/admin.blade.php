<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>
    {{-- Sisanya tetap sama --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="h-full">
<div class="flex h-full">
    <!-- Sidebar -->
    <div class="hidden md:flex md:w-64 md:flex-col bg-gray-800 text-white">
        <div class="flex flex-col flex-grow pt-5">
            <div class="flex items-center flex-shrink-0 px-4">
                <ion-icon name="server-outline" class="text-2xl mr-3 text-indigo-400"></ion-icon>
                <span class="text-xl font-semibold">Admin Panel</span>
            </div>
            <nav class="mt-5 flex-1 px-2 space-y-1">
                {{-- PERBAIKAN DI SINI: route nya sudah benar --}}
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <ion-icon name="grid-outline" class="mr-3 text-lg"></ion-icon>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <ion-icon name="people-outline" class="mr-3 text-lg"></ion-icon>
                    Manajemen Klien
                </a>
            </nav>
            {{-- Sisanya tetap sama --}}
            <div class="flex-shrink-0 flex border-t border-gray-700 p-4">
                <div class="flex-shrink-0 group block">
                    <div class="flex items-center">
                        <div>
                            <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-gray-300 group-hover:text-white">Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Main content -->
    <div class="flex flex-col flex-1">
        <main class="flex-1">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
