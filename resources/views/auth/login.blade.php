<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-[#F4F6F9] min-h-screen flex flex-col justify-between">
        
        <!-- Main Wrapper -->
        <div class="flex-1 flex items-center justify-center p-4 md:p-8">
            
            <!-- Login Card Container -->
            <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden grid grid-cols-1 md:grid-cols-2">
                
                <!-- Left Pane: Branding Info (Deep Blue) -->
                <div class="bg-[#002D84] p-8 md:p-12 flex flex-col justify-between items-center text-center text-white space-y-8 min-h-[450px]">
                    <!-- Title -->
                    <div class="space-y-1">
                        <h2 class="text-lg md:text-xl font-extrabold tracking-wider uppercase">
                            Sistem Manajemen Aset
                        </h2>
                        <h3 class="text-base md:text-lg font-bold tracking-wide opacity-90">
                            Diskominfotik Provinsi Riau
                        </h3>
                    </div>

                    <!-- Logo Emblem -->
                    <div class="flex-1 flex items-center justify-center">
                        <img 
                            src="{{ asset('images/logo-riau.svg') }}" 
                            alt="Lambang Riau" 
                            class="h-44 w-auto object-contain transition-transform duration-300 hover:scale-105"
                        >
                    </div>

                    <!-- Subtext -->
                    <p class="text-xs md:text-sm font-medium text-blue-100/80 leading-relaxed max-w-xs">
                        Integrasi data aset Diskominfotik Provinsi Riau dalam satu kendali digital yang aman dan transparan.
                    </p>
                </div>

                <!-- Right Pane: Login Form (White) -->
                <div class="p-8 md:p-12 flex flex-col justify-between bg-white">
                    <div>
                        <!-- Heading -->
                        <div class="mb-8">
                            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">
                                Selamat Datang
                            </h1>
                            <p class="text-xs text-slate-400 font-semibold mt-1">
                                Silahkan masuk menggunakan kredensial resmi Anda.
                            </p>
                        </div>

                        <!-- Form -->
                        <form method="POST" action="{{ route('login') }}" class="space-y-5">
                            @csrf

                            <!-- NIP Input -->
                            <div>
                                <label for="nip" class="block text-[9px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                                    NIP
                                </label>
                                <div class="relative flex items-center">
                                    <input 
                                        type="text" 
                                        name="nip" 
                                        id="nip" 
                                        value="{{ old('nip') }}"
                                        placeholder="Masukkan NIP" 
                                        required 
                                        autofocus
                                        class="w-full bg-slate-50 border @error('nip') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-xs py-3.5 pl-10 pr-4 text-slate-700 font-medium placeholder-slate-400 rounded-xl focus:outline-none focus:bg-white transition-all"
                                    >
                                    <!-- User Icon SVG -->
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                @error('nip')
                                    <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password Input -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label for="password" class="text-[9px] font-bold text-slate-400 tracking-wider uppercase">
                                        KATA SANDI
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-[9px] font-bold text-[#002D84] hover:text-[#0B2F83] tracking-wide uppercase transition-colors">
                                            LUPA KATA SANDI?
                                        </a>
                                    @endif
                                </div>
                                <div class="relative flex items-center">
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="password" 
                                        placeholder="••••••••" 
                                        required 
                                        class="w-full bg-slate-50 border @error('password') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-xs py-3.5 pl-10 pr-10 text-slate-700 font-medium placeholder-slate-300 rounded-xl focus:outline-none focus:bg-white transition-all"
                                    >
                                    <!-- Lock Icon SVG -->
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <!-- Toggle Eye Icon -->
                                    <button 
                                        type="button" 
                                        id="toggle-password" 
                                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    >
                                        <!-- Eye Icon SVG -->
                                        <svg id="eye-icon" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="w-full bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider py-4 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-md cursor-pointer hover:shadow-lg">
                                <span>Masuk ke Sistem</span>
                                <!-- Right Arrow SVG -->
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </form>
                    </div>

                    <!-- Catatan Info Box -->
                    <div class="bg-blue-50/40 border-l-4 border-[#002D84] rounded-r-xl p-4 flex items-start space-x-3 mt-6">
                        <!-- Info Icon SVG -->
                        <div class="text-[#002D84] mt-0.5 flex-shrink-0">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-slate-600 text-[10px] md:text-xs font-semibold leading-relaxed">
                            Catatan: Sistem akan mengarahkan Anda secara otomatis sesuai dengan hak akses yang terdaftar (<span class="text-[#002D84]">Super Admin</span>, <span class="text-[#002D84]">Admin Bidang</span>, atau <span class="text-[#002D84]">Pimpinan</span>).
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <footer class="w-full bg-[#F4F6F9] border-t border-slate-200/60 py-4 px-6 md:px-8 flex flex-col sm:flex-row justify-between items-center text-[10px] font-bold text-slate-400 tracking-wider">
            <div class="mb-2 sm:mb-0 text-center sm:text-left uppercase">
                &copy; 2024 DISKOMINFOTIK PROVINSI RIAU &bull; DIGITAL SOVEREIGN ASSET SYSTEM
            </div>
            <div class="flex space-x-4 md:space-x-6 justify-center uppercase">
                <a href="#" class="hover:text-[#002D84] transition-colors">KEBIJAKAN KEAMANAN</a>
                <span>|</span>
                <a href="#" class="hover:text-[#002D84] transition-colors">SYARAT DAN KETENTUAN</a>
                <span>|</span>
                <a href="#" class="hover:text-[#002D84] transition-colors">AKSES REGIONAL</a>
            </div>
        </footer>

        <!-- Eye toggle javascript logic -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const togglePassword = document.getElementById('toggle-password');
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eye-icon');

                togglePassword.addEventListener('click', function () {
                    // Toggle type attribute
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    // Toggle SVG paths for showing/hiding
                    if (type === 'text') {
                        // Eye-off icon representation (slash through eye)
                        eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        `;
                    } else {
                        // Standard eye icon
                        eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        `;
                    }
                });
            });
        </script>
    </body>
</html>
