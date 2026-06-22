<x-app-layout>
    @php
        $formatCurrency = fn ($value) => $value === null ? '-' : 'Rp ' . number_format((float) $value, 0, ',', '.');
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Riwayat Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Arsip aset Register dan SMKI bidang Anda yang sudah dinonaktifkan oleh Super Admin.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">
                    Daftar Aset Nonaktif
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Aset yang keluar dari inventaris aktif tetap tersimpan sebagai arsip bidang.
                </p>
            </div>
            <span class="inline-flex items-center self-start rounded-full bg-slate-50 border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600">
                {{ $assets->total() }} Riwayat
            </span>
        </div>

        <form action="{{ route('admin-perbidang.data-aset.riwayat') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label for="jenis" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                    Jenis Aset
                </label>
                <select id="jenis" name="jenis" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>Register</option>
                    <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
                </select>
            </div>

            <div class="md:col-span-3">
                <label for="search" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                    Cari Aset
                </label>
                <div class="relative">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Cari nama aset, kode aset, kategori, atau kondisi..."
                        class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
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

            @if($filters['search'] || $filters['jenis'] !== 'Semua Jenis')
                <div class="md:col-span-4">
                    <a href="{{ route('admin-perbidang.data-aset.riwayat') }}" class="text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold hover:underline">
                        Reset Filter
                    </a>
                </div>
            @endif
        </form>

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4 w-[34%]">Aset</th>
                        <th class="py-4 px-4">Jenis</th>
                        <th class="py-4 px-4">Kondisi</th>
                        <th class="py-4 px-4">Nilai Buku</th>
                        <th class="py-4 px-4 w-[28%]">Informasi Penghapusan</th>
                        <th class="py-4 px-4 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $asset->name }}</div>
                                <div class="text-[10px] text-slate-400 mt-1 flex flex-wrap gap-x-2">
                                    <span class="font-semibold text-slate-600">{{ $asset->code }}</span>
                                    <span>|</span>
                                    <span>{{ $asset->category ?? '-' }}</span>
                                </div>
                                @if($asset->deletion_reason)
                                    <div class="text-[10px] text-slate-500 mt-2 max-w-md">{{ $asset->deletion_reason }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                                    {{ $asset->type_label }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">
                                    {{ $asset->condition ?? '-' }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">
                                {{ $formatCurrency($asset->book_value) }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800">
                                    {{ $asset->deleted_at ? $asset->deleted_at->format('d M Y') : '-' }}
                                </div>
                                <div class="mt-1 text-[11px] text-slate-500">
                                    Metode: <span class="font-semibold text-slate-700">{{ $asset->deletion_method ?? '-' }}</span>
                                </div>
                                <div class="mt-1 text-[11px] text-slate-500">
                                    Oleh: <span class="font-semibold text-slate-700">{{ $asset->removed_by ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="{{ $asset->detail_route }}" class="inline-flex items-center justify-center text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Detail Aset">
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
                                Belum ada riwayat aset nonaktif untuk bidang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} aset nonaktif
                </div>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
