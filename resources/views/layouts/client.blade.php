<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    {{-- ... (bagian head tetap sama) ... --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', config('app.name'))</title>
    {{-- Tailwind sudah terbundel melalui Vite; hapus CDN yang memperlambat pemuatan --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        body { font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
        main { padding-bottom: 5rem; }
    </style>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="h-full">

    <div class="relative min-h-full">
        <main class="mb-[100em] md:mb-[4em]">
            @yield('content')
        </main>

        <nav class="fixed bottom-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-sm border-t border-gray-200 safe-area-bottom z-50">
            <div class="flex justify-around items-center h-full max-w-md mx-auto">
                {{-- Tautan Laporan Harian --}}
                <a href="{{ route('client.laporan.harian') }}" wire:navigate class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.laporan.harian') ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-500">
                    <ion-icon name="today-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Hari Ini</span>
                </a>

                {{-- Tautan Histori Laporan --}}
                <a href="{{ route('client.laporan.histori') }}" wire:navigate class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.laporan.histori') ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-500">
                    <ion-icon name="archive-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Histori</span>
                </a>

                {{-- Tautan Grafik --}}
                <a href="{{ route('client.grafik.index') }}" wire:navigate class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.grafik.*') ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-500">
                    <ion-icon name="stats-chart-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Grafik</span>
                </a>

                {{-- Tautan Profil --}}
                <a href="{{ route('client.profil.index') }}" wire:navigate class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.profil.*') ? 'text-blue-500' : 'text-gray-500' }} hover:text-blue-500">
                    <ion-icon name="person-circle-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Profil</span>
                </a>
            </div>
        </nav>
    </div>
    <script>

        function toggleFullscreen() {
            const elem = document.documentElement;

            // Cek apakah sedang dalam mode fullscreen
            if (!document.fullscreenElement) {
                // Jika tidak, masuk ke mode fullscreen
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) { /* Safari */
                    elem.webkitRequestFullscreen();
                }
            } else {
                // Jika iya, keluar dari mode fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) { /* Safari */
                    document.webkitExitFullscreen();
                }
            }
        }

        // Logika PWA
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const installButton = document.getElementById('install-app-button');
            if (installButton) {
                installButton.style.display = 'flex';
            }
        });

        function promptInstall() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(() => {
                    deferredPrompt = null;
                });
            }
        }
    </script>
    @livewireScripts
</body>
</html>
