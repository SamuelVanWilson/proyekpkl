<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100">
<head>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff"/>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        body { font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
        main { padding-bottom: 5rem; }
        /* Custom fullscreen styling: hide navigation and reset margins when app-fullscreen class is applied */
        html.app-fullscreen nav { display: none !important; }
        html.app-fullscreen main { margin: 0 !important; padding-bottom: 0 !important; }
        html.app-fullscreen .safe-area-bottom { padding-bottom: 0 !important; }
    </style>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="h-full">
    <div class="relative min-h-full">
        <main class="mb-[100em] md:mb-[4em] md:ml-20">
            @yield('content')
        </main>
        @php
            $hideNav = request()->routeIs('client.profil.edit') || request()->routeIs('client.laporan.preview') || request()->routeIs('client.laporan.preview.update');
        @endphp
        {{-- Navigasi bawah untuk mobile (md ke bawah) --}}
        <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-white/80 backdrop-blur-sm border-t border-gray-200 safe-area-bottom z-50 {{ $hideNav ? 'hidden' : '' }}">
            <div class="flex justify-around items-center h-full max-w-md mx-auto">
                @php
                    // Tentukan rute laporan berdasarkan status langganan pengguna.
                    $laporanRoute = auth()->check() && auth()->user()->hasActiveSubscription()
                        ? route('client.laporan.advanced')
                        : route('client.laporan.harian');
                    // Tentukan apakah salah satu halaman laporan sedang aktif.
                    $isLaporanActive = request()->routeIs('client.laporan.harian') || request()->routeIs('client.laporan.advanced');
                @endphp
                <a href="{{ $laporanRoute }}" class="flex flex-col items-center justify-center text-center w-full {{ $isLaporanActive ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                    <ion-icon name="document-text-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Laporan</span>
                </a>
                <a href="{{ route('client.laporan.histori') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.laporan.histori') ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                    <ion-icon name="archive-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Histori</span>
                </a>
                <a href="{{ route('client.grafik.index') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.grafik.*') ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                    <ion-icon name="stats-chart-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Grafik</span>
                </a>
                <a href="{{ route('client.profil.index') }}" class="flex flex-col items-center justify-center text-center w-full {{ request()->routeIs('client.profil.*') ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                    <ion-icon name="person-circle-outline" class="text-2xl"></ion-icon>
                    <span class="text-xs font-medium">Profil</span>
                </a>
            </div>
        </nav>
        {{-- Navigasi samping untuk desktop (md ke atas) --}}
        <nav class="hidden md:flex flex-col items-center py-4 px-2 bg-white/80 backdrop-blur-sm border-r border-gray-200 fixed top-0 bottom-0 left-0 w-20 z-50 {{ $hideNav ? 'hidden' : '' }}">
            @php
                // Tentukan rute dan status aktif untuk tab laporan di desktop.
                $laporanRoute = auth()->check() && auth()->user()->hasActiveSubscription()
                    ? route('client.laporan.advanced')
                    : route('client.laporan.harian');
                $isLaporanActive = request()->routeIs('client.laporan.harian') || request()->routeIs('client.laporan.advanced');
            @endphp
            <a href="{{ $laporanRoute }}" class="flex flex-col items-center mb-6 {{ $isLaporanActive ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                <ion-icon name="document-text-outline" class="text-2xl mb-1"></ion-icon>
                <span class="text-xs font-medium">Laporan</span>
            </a>
            <a href="{{ route('client.laporan.histori') }}" class="flex flex-col items-center mb-6 {{ request()->routeIs('client.laporan.histori') ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                <ion-icon name="archive-outline" class="text-2xl mb-1"></ion-icon>
                <span class="text-xs font-medium">Histori</span>
            </a>
            <a href="{{ route('client.grafik.index') }}" class="flex flex-col items-center mb-6 {{ request()->routeIs('client.grafik.*') ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                <ion-icon name="stats-chart-outline" class="text-2xl mb-1"></ion-icon>
                <span class="text-xs font-medium">Grafik</span>
            </a>
            <a href="{{ route('client.profil.index') }}" class="flex flex-col items-center {{ request()->routeIs('client.profil.*') ? 'text-green-500' : 'text-gray-500' }} hover:text-green-500">
                <ion-icon name="person-circle-outline" class="text-2xl mb-1"></ion-icon>
                <span class="text-xs font-medium">Profil</span>
            </a>
        </nav>
    </div>
    <script>
        document.addEventListener('livewire:navigating', () => {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.remove('hidden');
            }
        });
        document.addEventListener('livewire:navigated', () => {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        });
        /**
         * Toggle browser fullscreen mode and persist the state to localStorage.
         * When entering fullscreen the preference is stored under 'fullscreen-enabled'.
         * When exiting fullscreen the preference is removed. This allows the app to
         * automatically re‑enter fullscreen on subsequent page loads until the user
         * explicitly disables fullscreen again.
         */
        /**
         * Toggle browser fullscreen mode. When entering fullscreen, the
         * document element requests fullscreen and we add the app-fullscreen
         * CSS class. When exiting fullscreen we remove the class. The
         * preference is persisted to localStorage so the app re-enters
         * fullscreen on subsequent loads.
         */
        function toggleFullscreen() {
            const html = document.documentElement;
            // If no element is currently in fullscreen, request it.
            if (!document.fullscreenElement) {
                // Request fullscreen on the root element.
                const request = html.requestFullscreen || html.webkitRequestFullscreen || html.msRequestFullscreen;
                if (request) {
                    request.call(html).catch(() => {});
                }
                html.classList.add('app-fullscreen');
                localStorage.setItem('fullscreen-enabled', 'true');
            } else {
                // Exit fullscreen if currently enabled.
                const exit = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
                if (exit) {
                    exit.call(document).catch(() => {});
                }
                html.classList.remove('app-fullscreen');
                localStorage.removeItem('fullscreen-enabled');
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            // Re‑enter fullscreen automatically if preference was saved.
            if (localStorage.getItem('fullscreen-enabled') === 'true') {
                const html = document.documentElement;
                if (!document.fullscreenElement) {
                    const request = html.requestFullscreen || html.webkitRequestFullscreen || html.msRequestFullscreen;
                    if (request) {
                        request.call(html).catch(() => {});
                    }
                }
                html.classList.add('app-fullscreen');
            }
        });
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
