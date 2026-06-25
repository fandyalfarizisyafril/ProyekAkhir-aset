@php
    $notificationUser = Auth::user();
    $unreadCount = $notificationUser?->unreadNotifications()->count() ?? 0;
    $unreadNotifications = $notificationUser?->unreadNotifications()->latest()->take(5)->get() ?? collect();
@endphp

<header class="bg-white border-b border-slate-200 h-20 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-50">
    <!-- Left Section: Logo & System Name -->
    <div class="flex items-center gap-3 min-w-0">
        <button
            type="button"
            @click="sidebarOpen = !sidebarOpen"
            class="md:hidden h-10 w-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#0F3092]/20"
            aria-label="Buka menu"
            :aria-expanded="sidebarOpen.toString()"
        >
            <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
            <svg x-show="sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="flex-shrink-0">
            <img src="{{ asset('images/logo-riau.svg') }}" alt="Logo Riau" class="h-10 sm:h-12 w-auto object-contain">
        </div>
        <div class="flex flex-col min-w-0">
            <h1 class="text-[#0F3092] font-bold text-xs sm:text-sm md:text-base tracking-wide leading-tight truncate max-w-[11rem] sm:max-w-md">
                Sistem Manajemen Aset Diskominfotik
            </h1>
            <p class="text-[#0F3092] font-semibold text-[10px] sm:text-xs tracking-wider uppercase opacity-90 truncate">
                Provinsi Riau
            </p>
        </div>
    </div>

    <!-- Right Section: User Profile Info -->
    <div class="flex items-center space-x-3 flex-shrink-0">
        <div class="relative" x-data="{ notificationOpen: false }" @click.outside="notificationOpen = false">
            <button
                type="button"
                @click="notificationOpen = !notificationOpen"
                class="relative h-10 w-10 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:text-[#0F3092] hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-[#0F3092]/20 transition-colors"
                aria-label="Notifikasi"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0m6 0H9" />
                </svg>
                @if($unreadCount > 0)
                    <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">
                        {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                    </span>
                @endif
            </button>

            <div
                x-show="notificationOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                x-cloak
                class="absolute right-0 mt-3 w-[min(22rem,calc(100vw-2rem))] bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-50"
            >
                <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Notifikasi</h3>
                        <p class="text-[11px] text-slate-400 font-medium">{{ $unreadCount }} belum dibaca</p>
                    </div>
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.read-all') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-[10px] font-bold uppercase tracking-wider text-[#0F3092] hover:text-blue-700">
                                Tandai
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-80 overflow-y-auto">
                    @forelse($unreadNotifications as $notification)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="block border-b border-slate-100 last:border-b-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left px-4 py-3 hover:bg-blue-50/70 transition-colors">
                                <div class="flex items-start gap-3">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-[#0F3092] flex-shrink-0"></span>
                                    <span class="min-w-0">
                                        <span class="block text-xs font-bold text-slate-800 truncate">{{ $notification->data['title'] ?? 'Notifikasi Sistem' }}</span>
                                        <span class="block text-[11px] text-slate-500 mt-1 leading-relaxed line-clamp-2">{{ $notification->data['message'] ?? '-' }}</span>
                                        <span class="block text-[10px] text-slate-400 font-semibold mt-2">{{ $notification->created_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }}</span>
                                    </span>
                                </div>
                            </button>
                        </form>
                    @empty
                        <div class="px-4 py-8 text-center text-xs text-slate-400 font-medium">
                            Tidak ada notifikasi baru.
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('notifications.index') }}" class="block px-4 py-3 bg-slate-50 hover:bg-blue-50 text-center text-xs font-bold uppercase tracking-wider text-[#0F3092]">
                    Lihat Semua
                </a>
            </div>
        </div>

        <!-- User Profile Name & Role -->
        <div class="text-right hidden sm:block">
            <span class="block text-sm font-bold text-[#0F3092] leading-tight">
                {{ Auth::user()->nama ?? Auth::user()->name }}
            </span>
            <span class="block text-[10px] font-semibold text-slate-400 tracking-wider uppercase mt-0.5">
                {{ Auth::user()->role }}
            </span>
        </div>

        <!-- Custom Avatar Illustration SVG -->
        <div class="relative h-10 w-10 sm:h-11 sm:w-11 rounded-full border border-slate-200 shadow-sm overflow-hidden bg-[#E2E8F0] flex items-center justify-center">
            <!-- Cartoon Profile Avatar Illustration -->
            <svg class="h-full w-full object-cover" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Background Circle -->
                <circle cx="50" cy="50" r="50" fill="#EBF3FF"/>
                
                <!-- Hair (Back) -->
                <path d="M25 45C25 25 75 25 75 45C75 48 70 42 50 42C30 42 25 48 25 45Z" fill="#1A1D20"/>
                
                <!-- Ears -->
                <circle cx="28" cy="50" r="7" fill="#FCD3B0"/>
                <circle cx="72" cy="50" r="7" fill="#FCD3B0"/>
                
                <!-- Face -->
                <path d="M30 48C30 38 70 38 70 48C70 65 65 72 50 72C35 72 30 65 30 48Z" fill="#FDE2CD"/>
                
                <!-- Eyes -->
                <circle cx="42" cy="50" r="3.5" fill="#1F2937"/>
                <circle cx="58" cy="50" r="3.5" fill="#1F2937"/>
                
                <!-- Eyebrows -->
                <path d="M37 44C40 43 45 44 47 46" stroke="#1A1D20" stroke-width="2" stroke-linecap="round"/>
                <path d="M63 44C60 43 55 44 53 46" stroke="#1A1D20" stroke-width="2" stroke-linecap="round"/>
                
                <!-- Nose -->
                <path d="M50 51V58" stroke="#E2A27F" stroke-width="2.5" stroke-linecap="round"/>
                
                <!-- Mouth (Smile) -->
                <path d="M43 62C46 65 54 65 57 62" stroke="#E05B5B" stroke-width="2" stroke-linecap="round"/>
                
                <!-- Hair (Front/Bangs) -->
                <path d="M26 43C28 32 38 27 50 29C60 27 70 32 74 43C70 39 65 37 50 37C35 37 30 39 26 43Z" fill="#2D3139"/>
                <path d="M33 34C40 31 44 34 50 33C56 34 60 31 67 34C60 30 40 30 33 34Z" fill="#1A1D20"/>
                
                <!-- Neck -->
                <path d="M42 70V80H58V70H42Z" fill="#FCD3B0"/>
                
                <!-- Collar / Shirt (White) -->
                <path d="M35 80L50 68L65 80H35Z" fill="#FFFFFF"/>
                
                <!-- Suit (Blue) -->
                <path d="M15 80C15 76 25 74 33 76L50 82L67 76C75 74 85 76 85 80V100H15V80Z" fill="#0B2F83"/>
                
                <!-- Tie (Red) -->
                <path d="M47 77L50 72L53 77L51.5 98H48.5L47 77Z" fill="#E11D48"/>
                
                <!-- Collar fold outlines -->
                <path d="M33 76L44 86L50 82L56 86L67 76" stroke="#CBD5E1" stroke-width="1.5"/>
            </svg>
        </div>
    </div>
</header>
