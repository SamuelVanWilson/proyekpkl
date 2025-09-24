<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    {{-- Mencegah zoom di mobile agar terasa seperti aplikasi native --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Memuat Font San Francisco (standar iOS) dari Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- Menambahkan Heroicons untuk ikon ala iOS --}}
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <style>
        /* Menggunakan font SF Pro Display sebagai default */
        body {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        /* Style untuk safe area di iOS */
        .ios-safe-area-top { padding-top: env(safe-area-inset-top); }
        .ios-safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
        /* Custom fullscreen styling: hide elements and reset margins when app-fullscreen class is applied */
        html.app-fullscreen main { margin: 0 !important; padding-bottom: 0 !important; }
    </style>
    @vite('resources/css/app.css')

</head>
<body class="h-full">

    <div class="flex min-h-full flex-col justify-center bg-gray-50">
        {{-- Container utama untuk konten halaman --}}
        <main class="w-full max-w-md mx-auto p-6">
            @yield('content')
        </main>
    </div>

    <script>
    // Terapkan kelas app-fullscreen jika preferensi disimpan.
    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('fullscreen-enabled') === 'true') {
            document.documentElement.classList.add('app-fullscreen');
        }
    });
    </script>

</body>
</html>