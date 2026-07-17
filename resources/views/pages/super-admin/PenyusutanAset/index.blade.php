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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <x-dashboard.stats-card
            value="{{ $formatCurrency($summary['totalDepreciationExpense']) }}"
            title="Beban Penyusutan"
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
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6">
                <form action="{{ route('super-admin.penyusutan-aset.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[0.7fr_1fr_1fr_1fr_minmax(220px,1.25fr)_auto_auto] gap-3 items-end">
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

                    <div>
                        <label for="kategori" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            Kategori
                        </label>
                        <select id="kategori" name="kategori" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                            <option value="Semua Kategori" {{ $filters['kategori'] === 'Semua Kategori' ? 'selected' : '' }}>Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ $filters['kategori'] === $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status_penyusutan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            Status Penyusutan
                        </label>
                        <select id="status_penyusutan" name="status_penyusutan" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                            <option value="Semua Status" {{ $filters['status_penyusutan'] === 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                            <option value="Sudah Dihitung" {{ $filters['status_penyusutan'] === 'Sudah Dihitung' ? 'selected' : '' }}>Sudah Dihitung</option>
                            <option value="Belum Dihitung" {{ $filters['status_penyusutan'] === 'Belum Dihitung' ? 'selected' : '' }}>Belum Dihitung</option>
                        </select>
                    </div>

                    <div>
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

                    <div>
                        <button type="submit" class="w-full min-h-[44px] bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm whitespace-nowrap">
                            Terapkan Filter
                        </button>
                    </div>

                    @if($filters['search'] || $filters['bidang_id'] !== 'Semua Bidang' || $filters['kategori'] !== 'Semua Kategori' || $filters['status_penyusutan'] !== 'Semua Status' || $filters['tahun'] !== now()->year)
                        <div>
                            <a href="{{ route('super-admin.penyusutan-aset.index') }}" class="w-full min-h-[44px] border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 whitespace-nowrap">
                                Reset
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                <div class="responsive-table">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                                <th class="py-4 px-4">Aset</th>
                                <th class="py-4 px-4">Bidang</th>
                                <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Nilai Perolehan</th>
                                <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Nilai Awal</th>
                                <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Beban</th>
                                <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Nilai Buku</th>
                                <th class="py-4 px-4">Status</th>
                                <th class="py-4 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            @forelse($assets as $asset)
                                @php
                                    $depreciation = $asset->penyusutan->first();
                                    $acquisitionYear = $asset->tanggal_perolehan?->year ?? $asset->created_at?->year ?? $filters['tahun'];
                                    $depreciationPeriod = $depreciation ? max(0, $depreciation->tahun - $acquisitionYear + 1) : null;
                                    $suggestedUsefulLife = collect($usefulLifePresets)->first(function ($preset) use ($asset) {
                                        $category = mb_strtolower((string) $asset->kode_barang);

                                        return collect($preset['keywords'])->contains(fn ($keyword) => str_contains($category, $keyword));
                                    })['years'] ?? 5;
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
                                    <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">
                                        {{ $formatCurrency($asset->nilai) }}
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">
                                        {{ $depreciation ? $formatCurrency($depreciation->nilai_awal_tahun) : '-' }}
                                    </td>
                                    <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">
                                        {{ $depreciation ? $formatCurrency($depreciation->beban_penyusutan) : '-' }}
                                    </td>
                                    <td class="py-4 px-4 font-bold text-slate-800 whitespace-nowrap">
                                        {{ $depreciation ? $formatCurrency($depreciation->nilai_akhir_tahun) : '-' }}
                                        @if($depreciation)
                                            <div class="text-[10px] text-slate-400 font-medium mt-1">
                                                @if($depreciationPeriod === 0)
                                                    Sebelum tahun perolehan &bull; {{ $depreciation->tahun }}
                                                @else
                                                    {{ $depreciationPeriod <= $depreciation->umur_manfaat_tahun ? 'Tahun ke-' . $depreciationPeriod : 'Setelah umur manfaat' }} &bull; {{ $depreciation->tahun }}
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($depreciation)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Sudah Dihitung
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-1">
                                                {{ $depreciation->metode }} | {{ $depreciation->umur_manfaat_tahun }} tahun
                                            </div>
                                            <div class="text-[10px] text-slate-400 mt-1">
                                                Oleh {{ $depreciation->calculator->nama ?? $depreciation->calculator->name ?? '-' }}
                                                @if($depreciation->tanggal_hitung)
                                                    | {{ $depreciation->tanggal_hitung->format('d M Y H:i') }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Dihitung
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-1">
                                                Preset {{ $suggestedUsefulLife }} tahun
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center justify-center gap-1">
                                        @if($depreciation)
                                            <a href="{{ route('super-admin.penyusutan-aset.schedule', array_merge(['aset_register' => $asset->id], $filters)) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#0F3092] hover:bg-blue-50 hover:text-blue-800 transition-colors" title="Lihat Jadwal Penyusutan" aria-label="Lihat jadwal penyusutan {{ $asset->nama_aset }}">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </a>
                                        @endif
                                        <form action="{{ route('super-admin.penyusutan-aset.calculate', $asset->id) }}" method="POST" class="inline calculate-form" data-asset-name="{{ $asset->nama_aset }}">
                                            @csrf
                                            <input type="hidden" name="tahun" value="{{ $filters['tahun'] }}">
                                            <input type="hidden" name="bidang_id" value="{{ $filters['bidang_id'] }}">
                                            <input type="hidden" name="kategori" value="{{ $filters['kategori'] }}">
                                            <input type="hidden" name="status_penyusutan" value="{{ $filters['status_penyusutan'] }}">
                                            <input type="hidden" name="search" value="{{ $filters['search'] }}">
                                            <input type="hidden" name="umur_manfaat_mode" value="{{ $depreciation ? 'manual' : 'preset' }}">
                                            <input type="hidden" name="umur_manfaat_tahun" value="{{ $depreciation->umur_manfaat_tahun ?? old('umur_manfaat_tahun', $suggestedUsefulLife) }}">
                                            <input type="hidden" name="nilai_residu" value="{{ $depreciation->nilai_residu ?? old('nilai_residu', 0) }}">
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-[#0F3092] hover:text-blue-800 transition-colors hover:bg-blue-50" title="{{ $depreciation ? 'Hitung Ulang Penyusutan' : 'Hitung Penyusutan' }}">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h3m-6 4h12a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </form>
                                        </div>
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
                <input type="hidden" name="kategori" value="{{ $filters['kategori'] }}">
                <input type="hidden" name="status_penyusutan" value="{{ $filters['status_penyusutan'] }}">
                <input type="hidden" name="search" value="{{ $filters['search'] }}">

                <div>
                    <label for="umur_manfaat_mode" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Mode Umur Manfaat
                    </label>
                    <select
                        id="umur_manfaat_mode"
                        name="umur_manfaat_mode"
                        class="w-full bg-white border @error('umur_manfaat_mode') border-red-300 @else border-slate-200 @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                    >
                        <option value="preset" {{ old('umur_manfaat_mode', 'preset') === 'preset' ? 'selected' : '' }}>Otomatis berdasarkan kategori</option>
                        <option value="manual" {{ old('umur_manfaat_mode') === 'manual' ? 'selected' : '' }}>Manual untuk semua aset</option>
                    </select>
                    @error('umur_manfaat_mode')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div id="manual-useful-life-wrapper">
                    <label for="umur_manfaat_tahun" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Umur Manfaat Manual
                    </label>
                    <div id="manual-useful-life-field" class="flex items-stretch overflow-hidden rounded-xl border @error('umur_manfaat_tahun') border-red-300 @else border-slate-200 @enderror bg-white transition-colors focus-within:border-[#0F3092]">
                        <input
                            type="number"
                            id="umur_manfaat_tahun"
                            name="umur_manfaat_tahun"
                            value="{{ old('umur_manfaat_tahun', 5) }}"
                            min="1"
                            max="50"
                            class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-xs font-medium text-slate-700 focus:outline-none focus:ring-0"
                        >
                        <span class="flex items-center border-l border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-500" aria-hidden="true">
                            tahun
                        </span>
                    </div>
                    @error('umur_manfaat_tahun')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nilai_residu" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Nilai Residu
                    </label>
                    <div class="flex items-stretch overflow-hidden rounded-xl border @error('nilai_residu') border-red-300 @else border-slate-200 @enderror bg-white transition-colors focus-within:border-[#0F3092]">
                        <span class="flex items-center border-r border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-500" aria-hidden="true">
                            Rp
                        </span>
                        <input
                            type="number"
                            step="0.01"
                            id="nilai_residu"
                            name="nilai_residu"
                            value="{{ old('nilai_residu', 0) }}"
                            min="0"
                            class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-xs font-medium text-slate-700 focus:outline-none focus:ring-0"
                        >
                    </div>
                    @error('nilai_residu')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2">
                        Preset Kategori
                    </div>
                    <div class="space-y-1.5 text-[11px] text-slate-500 font-semibold">
                        @foreach($usefulLifePresets as $preset)
                            <div class="flex items-center justify-between gap-3">
                                <span>{{ $preset['label'] }}</span>
                                <span class="font-bold text-slate-700">{{ $preset['years'] }} tahun</span>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-1.5">
                            <span>Kategori lain</span>
                            <span class="font-bold text-slate-700">5 tahun</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                    Hitung Semua
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const usefulLifeMode = document.getElementById('umur_manfaat_mode');
            const usefulLifeInput = document.getElementById('umur_manfaat_tahun');
            const usefulLifeField = document.getElementById('manual-useful-life-field');
            const usefulLifeWrapper = document.getElementById('manual-useful-life-wrapper');

            const syncUsefulLifeInput = () => {
                if (!usefulLifeMode || !usefulLifeInput || !usefulLifeField || !usefulLifeWrapper) {
                    return;
                }

                const isManual = usefulLifeMode.value === 'manual';
                usefulLifeInput.disabled = !isManual;
                usefulLifeInput.required = isManual;
                usefulLifeWrapper.classList.toggle('opacity-50', !isManual);
                usefulLifeField.classList.toggle('bg-slate-50', !isManual);
                usefulLifeField.classList.toggle('bg-white', isManual);
            };

            syncUsefulLifeInput();
            usefulLifeMode?.addEventListener('change', syncUsefulLifeInput);

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
