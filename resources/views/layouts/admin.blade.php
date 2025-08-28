<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    {{-- PERBAIKAN: Menambahkan Alpine.js untuk membuat dropdown berfungsi --}}
    @vite('resources/css/app.css')
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        /* Tambahkan ini di file CSS utama Anda (e.g., app.css) */
        @layer components {
            .input-modern {
                @apply mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400
                    focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500;
            }
        }
    </style>
</head>
<body class="h-full">
<div class="flex h-full">
    <!-- Sidebar untuk Desktop -->
    <div class="hidden md:flex md:w-64 md:flex-col bg-gray-800 text-white">
        {{-- ... (Isi sidebar tetap sama) ... --}}
        <div class="flex flex-col flex-grow pt-5">
            <div class="flex items-center flex-shrink-0 px-4">
                <ion-icon name="server-outline" class="text-2xl mr-3 text-blue-400"></ion-icon>
                <span class="text-xl font-semibold">Admin Panel</span>
            </div>
            <nav class="mt-5 flex-1 px-2 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <ion-icon name="grid-outline" class="mr-3 text-lg"></ion-icon>
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700' }} group flex items-center px-2 py-2 text-sm font-medium rounded-md">
                    <ion-icon name="people-outline" class="mr-3 text-lg"></ion-icon>
                    Manajemen Klien
                </a>
            </nav>
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

    <!-- Konten Utama -->
    <div class="h-screen flex flex-col flex-1 overflow-y-scroll">
        <main class="flex-1 pb-16 md:pb-0">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>

    <!-- Navigasi Bawah untuk Mobile -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-white/95 backdrop-blur-sm border-t border-gray-200">
        <div class="flex justify-around items-center h-full">
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-500' }} hover:text-blue-600">
                <ion-icon name="grid-outline" class="text-2xl"></ion-icon>
                <span class="text-xs font-medium">Dashboard</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('admin.users.*') ? 'text-blue-600' : 'text-gray-500' }} hover:text-blue-600">
                <ion-icon name="people-outline" class="text-2xl"></ion-icon>
                <span class="text-xs font-medium">Klien</span>
            </a>
        </div>
    </nav>
</div>
<div id="loading-overlay" class="fixed inset-0 bg-white bg-opacity-75 z-[9999] hidden items-center justify-center">
    <div class="spinner"></div>
</div>
</body>
</html>
