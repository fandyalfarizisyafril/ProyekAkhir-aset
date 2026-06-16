<x-app-layout>
    @php
        $formatNumber = fn ($value) => number_format((int) $value, 0, ',', '.');
        $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Penyusutan Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Hitung nilai buku aset Register terverifikasi dengan metode garis lurus.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-emerald-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-dashboard.stats-card
            title="Aset Register"
            value="{{ $formatNumber($summary['eligibleCount']) }}"
            trend="Terverifikasi"
            type="info"
        />
        <x-dashboard.stats-card
            title="Sudah Dihitung"
            value="{{ $formatNumber($summary['calculatedCount']) }}"
            trend="Tahun {{ $filters['tahun'] }}"
            type="success"
        />
        <x-dashboard.stats-card
            title="Beban Penyusutan"
            value="{{ $formatCurrency($summary['totalDepreciationExpense']) }}"
            trend="Total tahun terpilih"
            type="danger"
        />
        <x-dashboard.stats-card
            title="Nilai Buku"
            value="{{ $formatCurrency($summary['totalBookValue']) }}"
            trend="Setelah penyusutan"
            type="success"
        />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-4">
            <form action="{{ route('super-admin.penyusutan-aset.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label for="tahun" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Tahun
                    </label>
                    <input
                        type="number"
                        id="tahun"
                        name="tahun"
                        value="{{ $filters['tahun'] }}"
                        min="2000"
                        max="{{ now()->year + 1 }}"
                        class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                    >
                </div>

                <div>
                    <label for="bidang_id" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Bidang
                    </label>
                    <select id="bidang_id" name="bidang_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                        @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>
                                {{ $bidang->nama_bidang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="search" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Cari Aset
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="Cari nama aset, kode aset, atau kategori..."
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

                <div class="md:col-span-4 flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
                        Terapkan Filter
                    </button>
                    @if($filters['search'] || $filters['bidang_id'] !== 'Semua Bidang' || $filters['tahun'] !== now()->year)
                        <a href="{{ route('super-admin.penyusutan-aset.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">
                    Hitung Massal
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Perhitungan mengikuti filter tahun, bidang, dan pencarian saat ini.
                </p>
            </div>

            <form action="{{ route('super-admin.penyusutan-aset.calculate-all') }}" method="POST" class="space-y-3 calculate-all-form">
                @csrf
                <input type="hidden" name="tahun" value="{{ $filters['tahun'] }}">
                <input type="hidden" name="bidang_id" value="{{ $filters['bidang_id'] }}">
                <input type="hidden" name="search" value="{{ $filters['search'] }}">

                <div>
                    <label for="umur_manfaat_tahun" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Umur Manfaat
                    </label>
                    <input
                        type="number"
                        id="umur_manfaat_tahun"
                        name="umur_manfaat_tahun"
                        value="{{ old('umur_manfaat_tahun', 5) }}"
                        min="1"
                        max="50"
                        class="w-full bg-white border @error('umur_manfaat_tahun') border-red-300 @else border-slate-200 @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                    >
                    @error('umur_manfaat_tahun')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nilai_residu" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Nilai Residu
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        id="nilai_residu"
                        name="nilai_residu"
                        value="{{ old('nilai_residu', 0) }}"
                        min="0"
                        class="w-full bg-white border @error('nilai_residu') border-red-300 @else border-slate-200 @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                    >
                    @error('nilai_residu')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                    Hitung Semua
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Aset</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Nilai Perolehan</th>
                        <th class="py-4 px-4">Nilai Awal</th>
                        <th class="py-4 px-4">Beban</th>
                        <th class="py-4 px-4">Nilai Buku</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($assets as $asset)
                        @php
                            $depreciation = $asset->penyusutan->first();
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $asset->nama_aset }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    <span class="font-semibold text-slate-600">{{ $asset->kode_aset }}</span>
                                    <span class="px-1">|</span>
                                    <span>{{ $asset->kode_barang }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ $asset->bidang->nama_bidang ?? '-' }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ $formatCurrency($asset->nilai) }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ $depreciation ? $formatCurrency($depreciation->nilai_awal_tahun) : '-' }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ $depreciation ? $formatCurrency($depreciation->beban_penyusutan) : '-' }}
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">
                                {{ $depreciation ? $formatCurrency($depreciation->nilai_akhir_tahun) : '-' }}
                            </td>
                            <td class="py-4 px-4">
                                @if($depreciation)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sudah Dihitung
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        {{ $depreciation->metode }} | {{ $depreciation->umur_manfaat_tahun }} tahun
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Dihitung
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <form action="{{ route('super-admin.penyusutan-aset.calculate', $asset->id) }}" method="POST" class="inline calculate-form" data-asset-name="{{ $asset->nama_aset }}">
                                    @csrf
                                    <input type="hidden" name="tahun" value="{{ $filters['tahun'] }}">
                                    <input type="hidden" name="bidang_id" value="{{ $filters['bidang_id'] }}">
                                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                    <input type="hidden" name="umur_manfaat_tahun" value="{{ $depreciation->umur_manfaat_tahun ?? old('umur_manfaat_tahun', 5) }}">
                                    <input type="hidden" name="nilai_residu" value="{{ $depreciation->nilai_residu ?? old('nilai_residu', 0) }}">
                                    <button type="submit" class="text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Hitung Penyusutan">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h3m-6 4h12a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada aset Register terverifikasi yang cocok dengan filter penyusutan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} aset
                </div>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.calculate-form, .calculate-all-form').forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const assetName = this.getAttribute('data-asset-name');
                    const title = assetName ? 'Hitung Penyusutan?' : 'Hitung Semua Penyusutan?';
                    const text = assetName
                        ? `Hitung penyusutan untuk aset "${assetName}"?`
                        : 'Hitung penyusutan untuk semua aset Register yang cocok dengan filter saat ini?';

                    Swal.fire({
                        title,
                        text,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#002D84',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, hitung',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>
