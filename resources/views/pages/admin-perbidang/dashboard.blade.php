<x-app-layout>
    @php
        $formatNumber = fn ($value) => number_format((int) $value, 0, ',', '.');
        $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $goodPercent = $summary['totalAssets'] > 0 ? round(($summary['goodCount'] / $summary['totalAssets']) * 100, 1) : 0;
    @endphp

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            Dashboard Admin Perbidang
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Ringkasan aset {{ $bidangName }} berdasarkan kategori dan kondisi terkini.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-dashboard.stats-card
            title="Total Aset Bidang"
            value="{{ $formatNumber($summary['totalAssets']) }}"
            trend="{{ $formatNumber($summary['registerCount']) }} Register, {{ $formatNumber($summary['smkiCount']) }} SMKI"
            type="info"
        />
        <x-dashboard.stats-card
            title="Aset Kondisi Baik"
            value="{{ $formatNumber($summary['goodCount']) }}"
            trend="{{ $goodPercent }}% dari aset terfilter"
            type="success"
        />
        <x-dashboard.stats-card
            title="Rusak / Perbaikan"
            value="{{ $formatNumber($summary['damagedCount']) }}"
            trend="{{ $formatNumber($summary['heavyDamageCount']) }} rusak berat"
            type="danger"
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6">
                <form action="{{ route('admin-perbidang.dashboard') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 md:items-end">
                    <div class="md:col-span-1">
                        <label for="kategori" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            Kategori
                        </label>
                        <div class="relative">
                            <select id="kategori" name="kategori" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                                <option value="Semua Kategori" {{ $filters['kategori'] === 'Semua Kategori' ? 'selected' : '' }}>Semua Kategori</option>
                                @foreach($categoryOptions as $category)
                                    <option value="{{ $category }}" {{ $filters['kategori'] === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <label for="kondisi" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            Kondisi
                        </label>
                        <div class="relative">
                            <select id="kondisi" name="kondisi" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                                <option value="Semua Kondisi" {{ $filters['kondisi'] === 'Semua Kondisi' ? 'selected' : '' }}>Semua Kondisi</option>
                                @foreach($conditionOptions as $condition)
                                    <option value="{{ $condition }}" {{ $filters['kondisi'] === $condition ? 'selected' : '' }}>{{ $condition }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 flex flex-col sm:flex-row gap-3">
                        <button type="submit" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Terapkan</span>
                        </button>
                        @if($filters['kategori'] !== 'Semua Kategori' || $filters['kondisi'] !== 'Semua Kondisi')
                            <a href="{{ route('admin-perbidang.dashboard') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl flex items-center justify-center transition-all duration-150">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 tracking-tight">
                            Sebaran Aset Per Kategori
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Gabungan kategori aset Register dan jenis barang SMKI pada bidang ini.
                        </p>
                    </div>
                    <span class="bg-slate-50 border border-slate-100 text-slate-600 text-[11px] font-semibold px-3 py-1.5 rounded-xl">
                        {{ $formatNumber($summary['totalAssets']) }} Unit
                    </span>
                </div>

                <div class="space-y-5">
                    @forelse($categoryStats as $category)
                        <div>
                            <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-1.5 gap-4">
                                <span class="tracking-wider text-[10px] uppercase text-slate-500 truncate">{{ $category['name'] }}</span>
                                <span class="shrink-0">{{ $formatNumber($category['count']) }} Unit</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                                <div class="bg-[#0F3092] h-full rounded-full transition-all duration-500" style="width: {{ $category['percentage'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-sm text-slate-400 font-medium py-10 bg-slate-50 rounded-xl border border-slate-100">
                            Belum ada data aset untuk filter ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6">
                    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">
                        Tipe Aset
                    </h3>
                    <div class="space-y-4">
                        @foreach($assetTypeStats as $type)
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-700 mb-1.5">
                                    <span>{{ $type['name'] }}</span>
                                    <span>{{ $formatNumber($type['count']) }}</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                                    <div class="{{ $type['color'] }} h-full rounded-full" style="width: {{ $type['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6">
                    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">
                        Status Data
                    </h3>
                    <div class="space-y-3 text-xs text-slate-600">
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span>Terverifikasi</span>
                            <strong class="text-slate-800">{{ $formatNumber($summary['verifiedCount']) }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span>Menunggu Verifikasi</span>
                            <strong class="text-slate-800">{{ $formatNumber($summary['pendingCount']) }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-100 pb-2">
                            <span>Sedang Dipinjam</span>
                            <strong class="text-slate-800">{{ $formatNumber($summary['borrowedCount']) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Nilai Aset Register</span>
                            <strong class="text-slate-800 text-right">{{ $formatCurrency($summary['totalRegisterValue']) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 h-full flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-6">
                        Kondisi Fisik
                    </h3>

                    <div class="relative flex justify-center items-center my-8">
                        <svg class="w-48 h-48 transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="30" fill="transparent" stroke="#F1F5F9" stroke-width="12"/>
                            @foreach($conditionStats as $condition)
                                @if($condition['count'] > 0)
                                    <circle
                                        cx="50"
                                        cy="50"
                                        r="30"
                                        fill="transparent"
                                        stroke="{{ $condition['color'] }}"
                                        stroke-width="12"
                                        stroke-dasharray="{{ $condition['dasharray'] }}"
                                        stroke-dashoffset="{{ $condition['dashoffset'] }}"
                                        stroke-linecap="round"
                                    />
                                @endif
                            @endforeach
                        </svg>

                        <div class="absolute flex flex-col items-center justify-center text-center">
                            <span class="text-3xl font-extrabold text-slate-800 tracking-tight leading-none">{{ $formatNumber($summary['totalAssets']) }}</span>
                            <span class="text-[9px] font-bold text-slate-400 tracking-widest uppercase mt-1">Total Unit</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-6 space-y-4">
                    @foreach($conditionStats as $condition)
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-3 text-xs font-semibold text-slate-600">
                                <span class="h-3 w-3 rounded-full inline-block" style="background-color: {{ $condition['color'] }}"></span>
                                <span>{{ $condition['name'] }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-800">
                                {{ $condition['percent'] }}% ({{ $formatNumber($condition['count']) }})
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
