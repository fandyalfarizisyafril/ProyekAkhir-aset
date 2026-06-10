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
    <body class="font-sans antialiased bg-[#F8FAFC] overflow-x-hidden">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col">
            <!-- New Premium Header Component -->
            <x-dashboard.header />

            <div
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-x-0 top-20 bottom-0 z-30 bg-slate-900/40 md:hidden"
                aria-hidden="true"
                x-cloak
            ></div>

            <div class="flex flex-1 min-w-0">
                <!-- New Dynamic Sidebar Component -->
                <x-dashboard.sidebar />

                <!-- Main Content Panel -->
                <div class="flex-1 min-w-0 flex flex-col justify-between min-h-[calc(100vh-5rem)]">
                    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
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
                    <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row justify-between items-center text-[10px] font-semibold text-slate-400 tracking-wider gap-3">
                        <div class="mb-2 sm:mb-0 text-center sm:text-left">
                            &copy; 2024 DISKOMINFOTIK PROVINSI RIAU &bull; DIGITAL SOVEREIGN ASSET SYSTEM
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-2 md:gap-x-6 justify-center">
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
