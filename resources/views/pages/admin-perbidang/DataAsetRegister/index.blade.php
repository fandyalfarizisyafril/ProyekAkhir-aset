<x-app-layout>
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Daftar Inventaris Aset Register / Fisik
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Kelola dan pantau seluruh aset operasional bidang Anda secara real-time.
            </p>
        </div>
        
        <!-- Tambah Aset Button & Ekspor Data -->
        <div class="flex items-center space-x-3 w-full sm:w-auto">
            <button type="button" class="w-1/2 sm:w-auto bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
                <!-- Export Icon -->
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Ekspor Data</span>
            </button>
            
            <a href="{{ route('admin-perbidang.data-aset-register.create') }}" class="w-1/2 sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
                <!-- Plus Icon -->
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Aset Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications (Fallback if JS disabled) -->
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

    <!-- Filters & Search Panel Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <!-- Tidy Filter Form -->
        <form action="{{ route('admin-perbidang.data-aset-register.index') }}" method="GET" id="filter-form" class="space-y-4">
            <!-- Dropdowns & Reset row -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <!-- Bidang Dropdown (Read-Only to enforce own Bidang view) -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                        <!-- Office Building Icon -->
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <select disabled class="w-full bg-slate-50 border border-slate-200 text-slate-400 text-xs rounded-xl pl-10 pr-8 py-2.5 appearance-none focus:outline-none transition-colors font-medium cursor-not-allowed">
                        <option selected>{{ auth()->user()->bidang->nama_bidang ?? 'Persandian' }}</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Status Dropdown -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                        <!-- Status/Check Shield Icon -->
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl pl-10 pr-8 py-2.5 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Status" {{ $status === 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                        <option value="Perlu Verifikasi" {{ $status === 'Perlu Verifikasi' ? 'selected' : '' }}>Perlu Verifikasi</option>
                        <option value="Terverifikasi" {{ $status === 'Terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
                        <option value="Ditolak" {{ $status === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Reset Filter Link -->
                @if($search || ($status && $status !== 'Semua Status'))
                    <a href="{{ route('admin-perbidang.data-aset-register.index') }}" class="text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold whitespace-nowrap pl-2 hover:underline">
                        Reset Filter
                    </a>
                @endif
            </div>

            <!-- Search input row -->
            <div class="relative w-full sm:w-96 shadow-sm rounded-xl">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}"
                    placeholder="Cari nama atau ID aset..."
                    class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-2.5 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                    <!-- Search Icon -->
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <!-- Submit Arrow Button inside Search Input -->
                <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#0F3092]" title="Cari">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Assets Table -->
        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">ID Aset / Kode</th>
                        <th class="py-4 px-4">Nama Aset</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Verifikasi</th>
                        <th class="py-4 px-4">Status Aset</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- ID Aset / Kode -->
                            <td class="py-4 px-4">
                                <span class="bg-[#F1F5F9] text-slate-800 font-bold px-3 py-1.5 rounded-lg border border-slate-200 tracking-wide inline-block">
                                    {{ $asset->kode_aset }}
                                </span>
                            </td>
                            
                            <!-- Nama Aset (Nama + subtext detail) -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ $asset->nama_aset }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 flex flex-wrap gap-x-2">
                                    <span>Kode Barang: <strong class="text-slate-600 font-semibold">{{ $asset->kode_barang }}</strong></span>
                                    <span>|</span>
                                    <span>Pengguna: <strong class="text-slate-600 font-semibold">{{ $asset->pengguna ?? '-' }}</strong></span>
                                    <span>|</span>
                                    <span>Lokasi: <strong class="text-slate-600 font-semibold">{{ $asset->lokasi_aset }}</strong></span>
                                </div>
                            </td>
                            
                            <!-- Bidang -->
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ $asset->bidang->nama_bidang ?? '-' }}
                            </td>
                            
                            <!-- Status Verifikasi -->
                            <td class="py-4 px-4">
                                @php
                                    switch ($asset->status_verifikasi) {
                                        case 'Terverifikasi':
                                            $statusBg = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            $dotBg = 'bg-emerald-500';
                                            break;
                                        case 'Perlu Verifikasi':
                                            $statusBg = 'bg-amber-50 text-amber-700 border border-amber-200';
                                            $dotBg = 'bg-amber-500';
                                            break;
                                        case 'Ditolak':
                                        default:
                                            $statusBg = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            $dotBg = 'bg-rose-500';
                                            break;
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 {{ $statusBg }}">
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $dotBg }}"></span>
                                    {{ $asset->status_verifikasi }}
                                </span>
                            </td>

                            <!-- Status Aset -->
                            <td class="py-4 px-4">
                                @php
                                    $operationalStatus = $asset->status ?: 'Tersedia';
                                    $operationalStatusLabel = $operationalStatus === 'Aktif' ? 'Tersedia' : $operationalStatus;
                                    switch ($operationalStatus) {
                                        case 'Dipinjam':
                                            $assetStatusBg = 'bg-sky-50 text-sky-700 border border-sky-200';
                                            $assetDotBg = 'bg-sky-500';
                                            break;
                                        case 'Maintenance':
                                            $assetStatusBg = 'bg-amber-50 text-amber-700 border border-amber-200';
                                            $assetDotBg = 'bg-amber-500';
                                            break;
                                        case 'Rusak':
                                            $assetStatusBg = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            $assetDotBg = 'bg-rose-500';
                                            break;
                                        case 'Aktif':
                                        case 'Tersedia':
                                            $assetStatusBg = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            $assetDotBg = 'bg-emerald-500';
                                            break;
                                        default:
                                            $assetStatusBg = 'bg-slate-50 text-slate-600 border border-slate-200';
                                            $assetDotBg = 'bg-slate-400';
                                            break;
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 {{ $assetStatusBg }}">
                                    <span class="mr-1.5 h-1.5 w-1.5 rounded-full {{ $assetDotBg }}"></span>
                                    {{ $operationalStatusLabel }}
                                </span>
                            </td>
                            
                            <!-- Action Buttons -->
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <!-- View/Detail Icon -->
                                    <a href="{{ route('admin-perbidang.data-aset-register.show', $asset->id) }}" class="text-slate-400 hover:text-slate-600 transition-colors p-1 hover:bg-slate-100 rounded" title="Detail Aset">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <!-- Edit Link -->
                                    <a href="{{ route('admin-perbidang.data-aset-register.edit', $asset->id) }}" class="text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Edit Aset">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada data aset register ditemukan untuk bidang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        @if($assets->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} unit aset
                </div>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- SweetAlert2 Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
