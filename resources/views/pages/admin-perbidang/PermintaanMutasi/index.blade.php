<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Permintaan Mutasi
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Ajukan kebutuhan aset kepada Super Admin tanpa melihat data aset bidang lain.
            </p>
        </div>

        <a href="{{ route('admin-perbidang.permintaan-mutasi.create') }}" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Buat Permintaan</span>
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-emerald-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-dashboard.stats-card title="Menunggu" value="{{ number_format($pendingCount) }}" trend="Menunggu Super Admin" type="info" />
        <x-dashboard.stats-card title="Dipenuhi" value="{{ number_format($fulfilledCount) }}" trend="Aset sudah dimutasikan" type="success" />
        <x-dashboard.stats-card title="Ditolak" value="{{ number_format($rejectedCount) }}" trend="Belum dapat dipenuhi" type="danger" />
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <form action="{{ route('admin-perbidang.permintaan-mutasi.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <select name="jenis" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Jenis</option>
                <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>Register</option>
                <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
            </select>

            <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                <option value="Semua Status" {{ $filters['status'] === 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                <option value="Menunggu Verifikasi" {{ $filters['status'] === 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="Dipenuhi" {{ $filters['status'] === 'Dipenuhi' ? 'selected' : '' }}>Dipenuhi</option>
                <option value="Ditolak" {{ $filters['status'] === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
            </select>

            <div class="relative md:col-span-2">
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari kebutuhan, kategori, atau lokasi..." class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
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
        </form>

        @if($filters['search'] || $filters['jenis'] !== 'Semua Jenis' || $filters['status'] !== 'Semua Status')
            <a href="{{ route('admin-perbidang.permintaan-mutasi.index') }}" class="inline-block text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold hover:underline">
                Reset Filter
            </a>
        @endif

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Kebutuhan</th>
                        <th class="py-4 px-4">Jenis</th>
                        <th class="py-4 px-4">Lokasi</th>
                        <th class="py-4 px-4">Tanggal</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($permintaan as $item)
                        @php
                            $statusClass = match ($item->status) {
                                'Dipenuhi' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                default => 'bg-amber-50 text-amber-700 border border-amber-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $item->nama_kebutuhan }}</div>
                                <div class="text-[10px] text-slate-400 mt-1 font-semibold">{{ $item->kategori_aset }}</div>
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-700 uppercase">{{ $item->jenis_aset }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600">{{ $item->lokasi_penggunaan }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600">{{ $item->tanggal_permintaan?->format('d M Y') }}</td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 {{ $statusClass }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('admin-perbidang.permintaan-mutasi.show', $item->id) }}" class="inline-flex text-slate-500 hover:text-slate-700 transition-colors p-1 hover:bg-slate-100 rounded" title="Detail Permintaan">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Belum ada permintaan mutasi aset.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permintaan->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $permintaan->firstItem() ?? 0 }}-{{ $permintaan->lastItem() ?? 0 }} dari {{ $permintaan->total() }} permintaan
                </div>
                <div>{{ $permintaan->links() }}</div>
            </div>
        @endif
    </div>
</x-app-layout>
