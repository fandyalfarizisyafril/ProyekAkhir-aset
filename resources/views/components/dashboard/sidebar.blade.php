@php
    $user = Auth::user();
    $role = $user->role;
    $currentRoute = request()->path();
    
    // Define menu items for each role
    $menuItems = [];
    
    if ($role === 'Super Admin') {
        $sidebarTitle = 'Super Admin';
        $menuItems = [
            [
                'name' => 'DASBORD',
                'url' => route('super-admin.dashboard'),
                'active' => request()->is('super-admin/dashboard'),
                'icon' => 'dashboard'
            ],
            [
                'name' => 'MANAJEMEN PENGGUNA',
                'url' => route('super-admin.pengguna.index'),
                'active' => request()->is('super-admin/pengguna*'),
                'icon' => 'users'
            ],
            [
                'name' => 'KATEGORI ASET',
                'url' => route('super-admin.kategori-aset.index'),
                'active' => request()->is('super-admin/kategori-aset*'),
                'icon' => 'categories'
            ],
            [
                'name' => 'VERIFIKASI',
                'url' => '#',
                'active' => request()->is('super-admin/verifikasi-aset*') || request()->is('super-admin/verifikasi-mutasi*') || request()->is('super-admin/verifikasi-peminjaman*'),
                'icon' => 'verification',
                'children' => [
                    [
                        'name' => 'VERIFIKASI ASET',
                        'url' => route('super-admin.verifikasi-aset.index'),
                        'active' => request()->is('super-admin/verifikasi-aset*')
                    ],
                    [
                        'name' => 'VERIFIKASI MUTASI',
                        'url' => route('super-admin.verifikasi-mutasi.index'),
                        'active' => request()->is('super-admin/verifikasi-mutasi*')
                    ],
                    [
                        'name' => 'VERIFIKASI PEMINJAMAN',
                        'url' => route('super-admin.verifikasi-peminjaman.index'),
                        'active' => request()->is('super-admin/verifikasi-peminjaman*')
                    ]
                ]
            ],
            [
                'name' => 'RIWAYAT MUTASI',
                'url' => route('riwayat-mutasi.index'),
                'active' => request()->is('riwayat-mutasi-aset*'),
                'icon' => 'mutasi'
            ],
            [
                'name' => 'REGISTRASI QR',
                'url' => route('super-admin.qr-code.index'),
                'active' => request()->is('super-admin/qr-code*'),
                'icon' => 'qr'
            ],
            [
                'name' => 'PENYUSUTAN ASET',
                'url' => route('super-admin.penyusutan-aset.index'),
                'active' => request()->is('super-admin/penyusutan-aset*'),
                'icon' => 'penyusutan'
            ],
            [
                'name' => 'PENGHAPUSAN ASET',
                'url' => route('super-admin.penghapusan-aset.index'),
                'active' => request()->is('super-admin/penghapusan-aset*'),
                'icon' => 'penghapusan'
            ]
        ];
    } elseif ($role === 'Admin Perbidang') {
        $bidangName = $user->bidang ? $user->bidang->nama_bidang : 'Persandian';
        $sidebarTitle = 'Admin Bidang ' . $bidangName;
        $menuItems = [
            [
                'name' => 'DASHBOARD',
                'url' => route('admin-perbidang.dashboard'),
                'active' => request()->is('admin-perbidang/dashboard'),
                'icon' => 'dashboard'
            ],
            [
                'name' => 'DATA ASET',
                'url' => '#',
                'active' => request()->is('admin-perbidang/data-aset-smki*') || request()->is('admin-perbidang/data-aset-register*'),
                'icon' => 'data-aset',
                'children' => [
                    [
                        'name' => 'DATA ASET SMKI',
                        'url' => route('admin-perbidang.data-aset-smki.index'),
                        'active' => request()->is('admin-perbidang/data-aset-smki*')
                    ],
                    [
                        'name' => 'DATA ASET REGISTER',
                        'url' => route('admin-perbidang.data-aset-register.index'),
                        'active' => request()->is('admin-perbidang/data-aset-register*')
                    ]
                ]
            ],
            [
                'name' => 'KONDISI ASET',
                'url' => route('admin-perbidang.kondisi-aset.index'),
                'active' => request()->is('admin-perbidang/kondisi-aset*'),
                'icon' => 'kondisi'
            ],
            [
                'name' => 'MUTASI ASET',
                'url' => route('admin-perbidang.mutasi-aset.index'),
                'active' => request()->is('admin-perbidang/mutasi-aset*'),
                'icon' => 'mutasi'
            ],
            [
                'name' => 'RIWAYAT MUTASI',
                'url' => route('riwayat-mutasi.index'),
                'active' => request()->is('riwayat-mutasi-aset*'),
                'icon' => 'mutasi'
            ],
            [
                'name' => 'PEMINJAMAN ASET',
                'url' => route('admin-perbidang.peminjaman-aset.index'),
                'active' => request()->is('admin-perbidang/peminjaman-aset*'),
                'icon' => 'peminjaman'
            ]
        ];
    } elseif ($role === 'Kepala Dinas') {
        $sidebarTitle = 'Pimpinan / Kepala Dinas';
        $menuItems = [
            [
                'name' => 'DASHBOARD',
                'url' => route('kepala-dinas.dashboard'),
                'active' => request()->is('kepala-dinas/dashboard'),
                'icon' => 'dashboard'
            ],
            [
                'name' => 'MONITORING ASET',
                'url' => '#',
                'active' => false,
                'icon' => 'monitoring',
                'children' => [
                    ['name' => 'DATA ASET', 'url' => '#', 'active' => false],
                    ['name' => 'KONDISI ASET', 'url' => '#', 'active' => false],
                    ['name' => 'STATUS ASET', 'url' => '#', 'active' => false]
                ]
            ],
            [
                'name' => 'LAPORAN',
                'url' => '#',
                'active' => false,
                'icon' => 'laporan'
            ],
            [
                'name' => 'RIWAYAT MUTASI',
                'url' => route('riwayat-mutasi.index'),
                'active' => request()->is('riwayat-mutasi-aset*'),
                'icon' => 'mutasi'
            ]
        ];
    } else {
        $sidebarTitle = 'Menu Pengguna';
        $menuItems = [
            [
                'name' => 'DASHBOARD',
                'url' => route('user.dashboard'),
                'active' => request()->is('user/dashboard'),
                'icon' => 'dashboard'
            ],
            [
                'name' => 'RIWAYAT MUTASI',
                'url' => route('riwayat-mutasi.index'),
                'active' => request()->is('riwayat-mutasi-aset*'),
                'icon' => 'mutasi'
            ]
        ];
    }
@endphp

<!-- Sidebar container -->
<aside
    class="fixed top-20 bottom-0 left-0 z-40 w-72 max-w-[85vw] bg-white border-r border-slate-200 flex flex-col justify-between flex-shrink-0 overflow-y-auto transform -translate-x-full transition-transform duration-200 ease-out md:sticky md:top-20 md:z-20 md:w-64 md:max-w-none md:h-[calc(100vh-5rem)] md:translate-x-0"
    :style="sidebarOpen ? 'transform: translateX(0)' : null"
    @keydown.escape.window="sidebarOpen = false"
>
    <!-- Top Part -->
    <div class="py-6 flex flex-col">
        <!-- Role Title -->
        <div class="px-6 mb-4">
            <h2 class="text-slate-800 font-bold text-base tracking-wide leading-snug">
                {{ $sidebarTitle }}
            </h2>
            <p class="text-slate-400 text-xs font-semibold uppercase tracking-wider mt-1">
                Menu
            </p>
        </div>

        <!-- Divider -->
        <div class="border-b border-slate-100 mx-6 mb-4"></div>

        <!-- Navigation Menu Items -->
        <nav class="space-y-1">
            @foreach($menuItems as $item)
                @if(isset($item['children']))
                    <!-- Dropdown Parent Item -->
                    <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }" class="flex flex-col">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-3 text-sm tracking-wide transition-all duration-150 {{ $item['active'] ? 'bg-blue-50/60 text-[#0F3092] font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800 font-semibold' }}">
                            <div class="flex items-center space-x-3">
                                @include('components.dashboard.sidebar-icons', ['icon' => $item['icon'], 'active' => $item['active']])
                                <span>{{ $item['name'] }}</span>
                            </div>
                            <svg class="h-4 w-4 transform transition-transform duration-150" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <!-- Dropdown Children -->
                        <div x-show="open" x-collapse class="pl-12 pr-6 py-1 space-y-1 bg-slate-50/50">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['url'] }}" @click="sidebarOpen = false" class="block py-2 text-xs font-medium tracking-wide transition-all duration-150 {{ $child['active'] ? 'text-[#0F3092] font-semibold' : 'text-slate-500 hover:text-slate-800' }}">
                                    &bull; {{ $child['name'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <!-- Simple Item -->
                    <a href="{{ $item['url'] }}" @click="sidebarOpen = false" class="flex items-center justify-between py-3 px-6 text-sm transition-all duration-150 {{ $item['active'] ? 'bg-blue-50/60 border-r-[4px] border-[#0F3092] text-[#0F3092] font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800 font-semibold' }}">
                        <div class="flex items-center space-x-3">
                            @include('components.dashboard.sidebar-icons', ['icon' => $item['icon'], 'active' => $item['active']])
                            <span>{{ $item['name'] }}</span>
                        </div>
                    </a>
                @endif
            @endforeach
        </nav>
    </div>

    <!-- Bottom Part: Settings & Logout -->
    <div class="py-6 flex flex-col space-y-1 border-t border-slate-100">
        <!-- Settings Link -->
        <a href="{{ route('profile.edit') }}" @click="sidebarOpen = false" class="flex items-center space-x-3 py-3 px-6 text-sm font-semibold tracking-wide text-slate-500 hover:bg-slate-50 hover:text-slate-800 transition-all duration-150 {{ request()->is('profile*') ? 'text-[#0F3092] font-bold' : '' }}">
            @include('components.dashboard.sidebar-icons', ['icon' => 'settings', 'active' => request()->is('profile*')])
            <span>Pengaturan</span>
        </a>

        <!-- Logout Form -->
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-3 py-3 px-6 text-sm font-semibold tracking-wide text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-150 text-left">
                @include('components.dashboard.sidebar-icons', ['icon' => 'logout', 'active' => false])
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>
