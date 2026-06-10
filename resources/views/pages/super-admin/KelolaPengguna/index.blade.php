<x-app-layout>
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Manajemen Pengguna
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Administrator dapat mengelola kredensial dan hak akses personil Diskominfotik Riau.
            </p>
        </div>
        
        <!-- Tambah Pengguna Button -->
        <a href="{{ route('super-admin.pengguna.create') }}" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
            <!-- User Plus Icon -->
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-emerald-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center space-x-3 text-rose-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Statistics Cards Grid (Strict mockup layout) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Pengguna Card -->
        <div class="bg-white rounded-2xl border-2 border-blue-600 shadow-sm p-6 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    TOTAL PENGGUNA
                </span>
                <span class="text-4xl font-extrabold text-slate-800 tracking-tight my-1">
                    {{ $totalUsers }}
                </span>
            </div>
            <div class="text-slate-400">
                <!-- Group icon -->
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <!-- Super Admin Card -->
        <div class="bg-white rounded-2xl border-2 border-emerald-600 shadow-sm p-6 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    Super Admin
                </span>
                <span class="text-4xl font-extrabold text-slate-800 tracking-tight my-1">
                    {{ $superAdminCount }}
                </span>
            </div>
            <div class="text-emerald-500">
                <!-- Checkmark shield icon -->
                <svg class="h-10 w-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>

        <!-- Ditangguhkan Card -->
        <div class="bg-white rounded-2xl border-2 border-rose-600 shadow-sm p-6 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    DITANGGUHKAN
                </span>
                <span class="text-4xl font-extrabold text-slate-800 tracking-tight my-1">
                    {{ $suspendedCount }}
                </span>
                <span class="text-[11px] font-medium text-slate-400">
                    Memerlukan tinjauan admin
                </span>
            </div>
            <div class="text-slate-400">
                <!-- Ban / suspended icon -->
                <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Table & Search Panel Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-6">
        
        <!-- Search Form -->
        <form action="{{ route('super-admin.pengguna.index') }}" method="GET" class="flex items-center gap-4">
            <div class="relative flex-1">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}"
                    placeholder="Cari berdasarkan Nama, NIP, atau Bidang..."
                    class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-4 py-3.5 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 pointer-events-none">
                    <!-- Search Icon -->
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm flex items-center justify-center">
                Cari
            </button>
        </form>

        <!-- User Table -->
        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Nama Pegawai</th>
                        <th class="py-4 px-4">NIP</th>
                        <th class="py-4 px-4">Email</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Role</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- Nama Pegawai with Profile SVG -->
                            <td class="py-4 px-4 flex items-center space-x-3">
                                <div class="h-9 w-9 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0">
                                    <!-- Default Vector Avatar -->
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span class="font-bold text-slate-800 leading-tight">
                                    {{ $user->nama }}
                                </span>
                            </td>
                            
                            <!-- NIP -->
                            <td class="py-4 px-4 font-semibold text-slate-600 tracking-wide">
                                {{ $user->nip }}
                            </td>
                            
                            <!-- Email -->
                            <td class="py-4 px-4 font-bold text-blue-600 hover:text-blue-800">
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </td>
                            
                            <!-- Bidang -->
                            <td class="py-4 px-4 font-medium text-slate-500">
                                {{ $user->bidang ? $user->bidang->nama_bidang : '-' }}
                            </td>
                            
                            <!-- Role Badge -->
                            <td class="py-4 px-4">
                                @php
                                    switch ($user->role) {
                                        case 'Super Admin':
                                            $badgeClass = 'bg-blue-600 text-white';
                                            $roleText = 'SUPER ADMIN';
                                            break;
                                        case 'Admin Perbidang':
                                            $badgeClass = 'bg-[#EBF3FF] text-[#0F3092] border border-[#CBD5E1]';
                                            $roleText = 'ADMIN BIDANG';
                                            break;
                                        case 'Kepala Dinas':
                                            $badgeClass = 'bg-purple-100 text-purple-700';
                                            $roleText = 'KEDIS';
                                            break;
                                        case 'User':
                                        default:
                                            $badgeClass = 'bg-slate-100 text-slate-600 border border-slate-200';
                                            $roleText = 'OPERATOR';
                                            break;
                                    }
                                @endphp
                                <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md {{ $badgeClass }}">
                                    {{ $roleText }}
                                </span>
                            </td>
                            
                            <!-- Status -->
                            <td class="py-4 px-4">
                                @php
                                    switch ($user->status) {
                                        case 'Aktif':
                                            $statusBg = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            $dotBg = 'bg-emerald-500';
                                            break;
                                        case 'Non-Aktif':
                                            $statusBg = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            $dotBg = 'bg-rose-500';
                                            break;
                                        case 'Ditangguhkan':
                                        default:
                                            $statusBg = 'bg-amber-50 text-amber-700 border border-amber-200';
                                            $dotBg = 'bg-amber-500';
                                            break;
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold leading-5 {{ $statusBg }}">
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $dotBg }}"></span>
                                    {{ $user->status }}
                                </span>
                            </td>
                            
                            <!-- Action Buttons -->
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <!-- Edit Link -->
                                    <a href="{{ route('super-admin.pengguna.edit', $user->id) }}" class="text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Edit Pengguna">
                                        <!-- Edit SVG Icon -->
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    
                                    <!-- Delete Link -->
                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('super-admin.pengguna.destroy', $user->id) }}" method="POST" class="inline delete-form" data-user-name="{{ $user->nama }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700 transition-colors p-1 hover:bg-red-50 rounded cursor-pointer" title="Hapus Pengguna">
                                                <!-- Trash SVG Icon -->
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <!-- Empty spacer to keep grid aligned -->
                                        <div class="w-7"></div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada data pengguna ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        @if($users->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} pengguna
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif

    </div>

    <!-- SweetAlert2 Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Delete confirmation using SweetAlert2
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const userName = this.getAttribute('data-user-name');
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: `Pengguna "${userName}" akan dihapus secara permanen!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // Success & Error alerts using SweetAlert2
            @if(session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonColor: '#002D84',
                    confirmButtonText: 'OK'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonColor: '#002D84',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>
</x-app-layout>
