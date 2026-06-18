<x-app-layout>
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Daftar Kondisi Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Pantau dan kelola status pemeliharaan aset di seluruh unit kerja Anda.
            </p>
        </div>
        
        <!-- Tambah Riwayat Button -->
        <div class="flex items-center space-x-3 w-full sm:w-auto">
            <a href="{{ route('admin-perbidang.kondisi-aset.create') }}" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
                <!-- Plus Icon -->
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Catat Riwayat Baru</span>
            </a>
        </div>
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

    <!-- Filters & List Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <form action="{{ route('admin-perbidang.kondisi-aset.index') }}" method="GET" id="filter-form" class="space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Dropdown Filters Row -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Kategori Dropdown -->
                    <div class="relative w-full sm:w-56">
                        <select name="kategori" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl pl-4 pr-10 py-2.5 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                            <option value="Semua Kategori" {{ $kategori === 'Semua Kategori' ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="REGISTER" {{ $kategori === 'REGISTER' ? 'selected' : '' }}>REGISTER</option>
                            <option value="SMKI" {{ $kategori === 'SMKI' ? 'selected' : '' }}>SMKI</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Kondisi Dropdown -->
                    <div class="relative w-full sm:w-56">
                        <select name="kondisi" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl pl-4 pr-10 py-2.5 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                            <option value="Semua Kondisi" {{ $kondisi === 'Semua Kondisi' ? 'selected' : '' }}>Semua Kondisi</option>
                            <option value="Baik" {{ $kondisi === 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak Ringan" {{ $kondisi === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Berat" {{ $kondisi === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Reset Filter Link -->
                    @if($search || ($kategori && $kategori !== 'Semua Kategori') || ($kondisi && $kondisi !== 'Semua Kondisi'))
                        <a href="{{ route('admin-perbidang.kondisi-aset.index') }}" class="text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold whitespace-nowrap pl-2 hover:underline">
                            Reset Filter
                        </a>
                    @endif
                </div>

                <!-- Search Input Row -->
                <div class="relative w-full md:w-80 shadow-sm rounded-xl">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $search }}"
                        placeholder="Cari nama atau kode aset..."
                        class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-2.5 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#0F3092]" title="Cari">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>

        <!-- Table View -->
        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Nama Aset & Kode</th>
                        <th class="py-4 px-4">Kategori</th>
                        <th class="py-4 px-4">Lokasi</th>
                        <th class="py-4 px-4">Kondisi</th>
                        <th class="py-4 px-4">Update Terakhir</th>
                        <th class="py-4 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- Nama Aset & Kode -->
                            <td class="py-4 px-4">
                                <div class="flex items-center space-x-3.5">
                                    <!-- Dynamic hardware icon based on name -->
                                    @php
                                        $assetName = strtolower($asset->name);
                                        if (str_contains($assetName, 'macbook') || str_contains($assetName, 'laptop') || str_contains($assetName, 'notebook') || str_contains($assetName, 'pc') || str_contains($assetName, 'computer') || str_contains($assetName, 'server') || str_contains($assetName, 'dell')) {
                                            $iconBg = 'bg-[#EFF6FF] text-[#2563EB]';
                                            $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>';
                                        } elseif (str_contains($assetName, 'printer') || str_contains($assetName, 'epson') || str_contains($assetName, 'canon')) {
                                            $iconBg = 'bg-[#F1F5F9] text-[#475569]';
                                            $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>';
                                        } elseif (str_contains($assetName, 'router') || str_contains($assetName, 'cisco') || str_contains($assetName, 'switch') || str_contains($assetName, 'network') || str_contains($assetName, 'hub') || str_contains($assetName, 'ap') || str_contains($assetName, 'access point')) {
                                            $iconBg = 'bg-[#EEF2F6] text-[#4F46E5]';
                                            $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a10.5 10.5 0 0114.14 0M1.414 6.586a16.5 16.5 0 0121.172 0" />
                                            </svg>';
                                        } else {
                                            $iconBg = 'bg-[#F0FDFA] text-[#0D9488]';
                                            $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>';
                                        }
                                    @endphp
                                    <div class="h-10 w-10 {{ $iconBg }} rounded-xl flex items-center justify-center flex-shrink-0">
                                        {!! $iconSvg !!}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">
                                            {{ $asset->name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-semibold tracking-wide mt-0.5 uppercase">
                                            {{ $asset->code }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="py-4 px-4">
                                @if($asset->category === 'REGISTER')
                                    <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded border border-blue-200 tracking-wide inline-block uppercase">
                                        REGISTER
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2.5 py-1 rounded border border-slate-200 tracking-wide inline-block uppercase">
                                        SMKI
                                    </span>
                                @endif
                            </td>

                            <!-- Lokasi -->
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ $asset->location }}
                            </td>

                            <!-- Kondisi -->
                            <td class="py-4 px-4">
                                @php
                                    switch ($asset->condition) {
                                        case 'Baik':
                                            $dotColor = 'bg-emerald-500';
                                            $textColor = 'text-slate-700';
                                            break;
                                        case 'Rusak Ringan':
                                            $dotColor = 'bg-amber-500';
                                            $textColor = 'text-slate-700';
                                            break;
                                        case 'Rusak Berat':
                                        default:
                                            $dotColor = 'bg-rose-500';
                                            $textColor = 'text-slate-700';
                                            break;
                                    }
                                @endphp
                                <span class="inline-flex items-center font-bold text-xs {{ $textColor }}">
                                    <span class="mr-2 h-2.5 w-2.5 rounded-full {{ $dotColor }} border border-white shadow-sm"></span>
                                    {{ $asset->condition }}
                                </span>
                            </td>

                            <!-- Update Terakhir -->
                            <td class="py-4 px-4 font-medium text-slate-500">
                                {{ \Carbon\Carbon::parse($asset->last_update)->translatedFormat('d M Y') }}
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('admin-perbidang.kondisi-aset.edit', ['kondisi_aset' => $asset->id, 'type' => $asset->category]) }}" class="text-[#0F3092] hover:text-[#0B2F83] text-[11px] font-extrabold uppercase tracking-wider flex items-center justify-end space-x-1.5 transition-colors">
                                    <span>UPDATE KONDISI</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada data aset ditemukan untuk bidang Anda.
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

    <!-- SweetAlert2 Alerts for Notifications -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
