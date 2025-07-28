<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    {{-- Mengambil judul dari section, dengan fallback --}}
    <title>@yield('title', config('app.name'))</title>

    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Memuat Font San Francisco (standar iOS) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- Memuat Ion-Icons untuk ikon ala iOS --}}
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        /* Style untuk safe area di iOS */
        .safe-area-top { padding-top: env(safe-area-inset-top); }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
        .pb-24 { padding-bottom: 6rem; } /* Padding bottom agar konten tidak tertutup nav bawah */
    </style>
</head>
<body class="h-full">

    <div class="relative min-h-full">
        {{-- Konten utama yang bisa di-scroll --}}
        <main class="pb-24">
            @yield('content')
        </main>

        {{-- Bilah Navigasi Bawah (Bottom Navigation Bar) --}}
        <nav class="fixed bottom-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-sm border-t border-gray-200 safe-area-bottom">
            <div class="flex justify-around items-center h-full max-w-md mx-auto">
                {{-- Tautan Laporan --}}
                <a href="{{ route('client.laporan.index') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.laporan.*') ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-500 transition-colors">
                    <ion-icon name="document-text-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Laporan</span>
                </a>

                {{-- Tautan Grafik --}}
                <a href="{{ route('client.grafik.index') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.grafik.*') ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-500 transition-colors">
                    <ion-icon name="stats-chart-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Grafik</span>
                </a>

                {{-- Tautan Profil --}}
                <a href="{{ route('client.profil.index') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.profil.*') ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-500 transition-colors">
                    <ion-icon name="person-circle-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Profil</span>
                </a>
            </div>
        </nav>
    </div>

</body>
</html>
