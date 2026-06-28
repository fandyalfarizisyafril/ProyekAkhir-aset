<x-app-layout>
    @php
        $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $hasFilter = $filters['tahun'] !== now()->year
            || $filters['bidang_id'] !== 'Semua Bidang'
            || $filters['kategori'] !== 'Semua Kategori'
            || filled($filters['search']);
    @endphp

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Monitoring Penyusutan Aset</h2>
        <p class="text-sm text-slate-500 mt-1">
            Pantau nilai penyusutan aset Register terverifikasi tanpa mengubah hasil perhitungan.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Nilai Perolehan</p>
            <div class="mt-2 text-xl font-extrabold text-slate-800 whitespace-nowrap">{{ $formatCurrency($summary['totalAcquisitionValue']) }}</div>
            <p class="text-xs text-slate-400 mt-1">{{ $summary['assetCount'] }} aset Register aktif</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Beban Penyusutan</p>
            <div class="mt-2 text-xl font-extrabold text-slate-800 whitespace-nowrap">{{ $formatCurrency($summary['totalDepreciationExpense']) }}</div>
            <p class="text-xs text-slate-400 mt-1">Tahun {{ $filters['tahun'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Nilai Buku</p>
            <div class="mt-2 text-xl font-extrabold text-slate-800 whitespace-nowrap">{{ $formatCurrency($summary['totalBookValue']) }}</div>
            <p class="text-xs text-slate-400 mt-1">{{ $summary['calculatedCount'] }} dari {{ $summary['assetCount'] }} aset sudah dihitung</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 mb-6">
        <form action="{{ route('kepala-dinas.monitoring-aset.penyusutan') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[0.75fr_1fr_1fr_minmax(220px,1.3fr)_auto] gap-3 items-end">
            <div>
                <label for="tahun" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Tahun</label>
                <select id="tahun" name="tahun" class="w-full min-h-[44px] bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092]">
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ (int) $filters['tahun'] === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="bidang_id" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Bidang</label>
                <select id="bidang_id" name="bidang_id" class="w-full min-h-[44px] bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092]">
                    <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                    @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>{{ $bidang->nama_bidang }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="kategori" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kategori</label>
                <select id="kategori" name="kategori" class="w-full min-h-[44px] bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092]">
                    <option value="Semua Kategori" {{ $filters['kategori'] === 'Semua Kategori' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ $filters['kategori'] === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="search" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Cari Aset</label>
                <div class="relative">
                    <input id="search" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama, kode aset, kategori, atau bidang..." class="w-full min-h-[44px] bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-4 py-3 focus:outline-none focus:border-[#0F3092]">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <button type="submit" class="min-h-[44px] bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-colors whitespace-nowrap">
                Terapkan Filter
            </button>
        </form>

        @if($hasFilter)
            <a href="{{ route('kepala-dinas.monitoring-aset.penyusutan') }}" class="inline-block mt-4 text-[#0F3092] text-xs font-semibold hover:underline">Reset Filter</a>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-800">Daftar Penyusutan Aset</h3>
                <p class="text-xs text-slate-400 mt-1">Hasil perhitungan metode garis lurus untuk tahun {{ $filters['tahun'] }}.</p>
            </div>
            <span class="inline-flex self-start rounded-full bg-slate-50 border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600">
                {{ $assets->total() }} Aset
            </span>
        </div>

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Aset</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4 min-w-[125px] whitespace-nowrap">Nilai Perolehan</th>
                        <th class="py-4 px-4 min-w-[100px] whitespace-nowrap">Umur Manfaat</th>
                        <th class="py-4 px-4 min-w-[125px] whitespace-nowrap">Beban</th>
                        <th class="py-4 px-4 min-w-[125px] whitespace-nowrap">Nilai Buku</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($assets as $asset)
                        @php($depreciation = $asset->penyusutan->first())
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $asset->nama_aset }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    <span class="font-semibold text-slate-600">{{ $asset->kode_aset }}</span>
                                    <span class="px-1">|</span>
                                    <span>{{ $asset->kode_barang }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-500">{{ $asset->bidang->nama_bidang ?? '-' }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $formatCurrency($asset->nilai) }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $depreciation ? $depreciation->umur_manfaat_tahun . ' tahun' : '-' }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $depreciation ? $formatCurrency($depreciation->beban_penyusutan) : '-' }}</td>
                            <td class="py-4 px-4 font-bold text-slate-800 whitespace-nowrap">{{ $depreciation ? $formatCurrency($depreciation->nilai_akhir_tahun) : $formatCurrency($asset->nilai) }}</td>
                            <td class="py-4 px-4">
                                @if($depreciation)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Sudah Dihitung</span>
                                    <div class="text-[10px] text-slate-400 mt-1">{{ $depreciation->metode }}</div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Belum Dihitung</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('kepala-dinas.monitoring-aset.penyusutan.show', array_merge(['asetRegister' => $asset->id], $filters)) }}" class="inline-flex items-center justify-center h-9 w-9 rounded-lg text-[#0F3092] hover:bg-blue-50 transition-colors" title="Lihat riwayat penyusutan" aria-label="Lihat riwayat penyusutan {{ $asset->nama_aset }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada aset Register terverifikasi yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="border-t border-slate-100 pt-4">{{ $assets->links() }}</div>
        @endif
    </div>
</x-app-layout>
