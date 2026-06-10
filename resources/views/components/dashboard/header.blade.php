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
