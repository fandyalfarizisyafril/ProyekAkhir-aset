<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#F8FAFC]">
        <div class="min-h-screen flex flex-col">
            <!-- New Premium Header Component -->
            <x-dashboard.header />

            <div class="flex flex-1 flex-col md:flex-row">
                <!-- New Dynamic Sidebar Component -->
                <x-dashboard.sidebar />

                <!-- Main Content Panel -->
                <div class="flex-1 flex flex-col justify-between min-h-[calc(100vh-5rem)]">
                    <main class="flex-1 p-6 md:p-8">
                        @isset($header)
                            <!-- Page Title/Header -->
                            <div class="mb-6">
                                {{ $header }}
                            </div>
                        @endisset

                        <!-- Main Slot Content -->
                        {{ $slot }}
                    </main>

                    <!-- Dashboard Footer (Mockup Style) -->
                    <footer class="bg-white border-t border-slate-200 py-4 px-6 md:px-8 flex flex-col sm:flex-row justify-between items-center text-[10px] font-semibold text-slate-400 tracking-wider">
                        <div class="mb-2 sm:mb-0 text-center sm:text-left">
                            &copy; 2024 DISKOMINFOTIK PROVINSI RIAU &bull; DIGITAL SOVEREIGN ASSET SYSTEM
                        </div>
                        <div class="flex space-x-4 md:space-x-6 justify-center">
                            <a href="#" class="hover:text-[#0F3092] transition-colors">KEBIJAKAN KEAMANAN</a>
                            <span>|</span>
                            <a href="#" class="hover:text-[#0F3092] transition-colors">SYARAT DAN KETENTUAN</a>
                            <span>|</span>
                            <a href="#" class="hover:text-[#0F3092] transition-colors">AKSES REGIONAL</a>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>
