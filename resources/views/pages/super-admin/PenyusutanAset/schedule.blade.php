<x-app-layout>
    @php
        $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Jadwal Penyusutan Aset</h2>
            <p class="text-sm text-slate-500 mt-1">Proyeksi nilai buku dari tahun pertama hingga akhir umur manfaat.</p>
        </div>
        <a href="{{ route('super-admin.penyusutan-aset.index', $backFilters) }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-colors">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-6">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-5">
            <div class="min-w-0">
                <span class="inline-flex px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-blue-50 text-blue-700 border border-blue-200">REGISTER</span>
                <h3 class="text-xl font-extrabold text-slate-800 mt-3">{{ $asset->nama_aset }}</h3>
                <p class="text-xs text-slate-400 font-semibold mt-1">
                    <span class="text-slate-600">{{ $asset->kode_aset }}</span>
                    <span class="px-1">|</span>
                    <span>{{ $asset->kode_barang }}</span>
                    <span class="px-1">|</span>
                    <span>{{ $usefulLifeCategoryLabel }}</span>
                </p>
                <p class="text-xs text-slate-500 mt-2">{{ $asset->bidang->nama_bidang ?? '-' }}</p>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 w-full xl:w-auto">
                <div class="min-w-[145px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nilai Perolehan</span>
                    <strong class="block text-sm text-slate-800 mt-1 whitespace-nowrap">{{ $formatCurrency($asset->nilai) }}</strong>
                </div>
                <div class="min-w-[125px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Umur Manfaat</span>
                    <strong class="block text-sm text-slate-800 mt-1">{{ $depreciation->umur_manfaat_tahun }} tahun</strong>
                </div>
                <div class="min-w-[145px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Mulai Penyusutan</span>
                    <strong class="block text-sm text-slate-800 mt-1 whitespace-nowrap">
                        {{ $asset->tanggal_perolehan?->format('d M Y') ?? $asset->created_at?->format('d M Y') ?? '-' }}
                    </strong>
                </div>
                <div class="min-w-[145px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nilai Residu</span>
                    <strong class="block text-sm text-slate-800 mt-1 whitespace-nowrap">{{ $formatCurrency($depreciation->nilai_residu) }}</strong>
                </div>
                <div class="min-w-[145px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Beban per Tahun</span>
                    <strong class="block text-sm text-slate-800 mt-1 whitespace-nowrap">{{ $formatCurrency($schedule[0]->expense ?? 0) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">Proyeksi Penyusutan</h3>
                <p class="text-xs text-slate-400 mt-1">
                    Berdasarkan metode {{ $depreciation->metode }}, parameter perhitungan tahun {{ $depreciation->tahun }}.
                </p>
            </div>
            <span class="inline-flex self-start rounded-full bg-blue-50 border border-blue-200 px-3 py-1.5 text-[10px] font-bold text-blue-700">
                Tahun dipilih: {{ $selectedPeriod > 0 ? 'ke-' . $selectedPeriod : 'sebelum perolehan' }} &bull; {{ $depreciation->tahun }}
            </span>
        </div>

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Periode</th>
                        <th class="py-4 px-4">Tahun</th>
                        <th class="py-4 px-4 min-w-[130px] whitespace-nowrap">Nilai Awal</th>
                        <th class="py-4 px-4 min-w-[130px] whitespace-nowrap">Beban</th>
                        <th class="py-4 px-4 min-w-[145px] whitespace-nowrap">Akumulasi Penyusutan</th>
                        <th class="py-4 px-4 min-w-[130px] whitespace-nowrap">Nilai Buku</th>
                        <th class="py-4 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @foreach($schedule as $row)
                        @php($isSelected = $row->year === $depreciation->tahun)
                        <tr class="{{ $isSelected ? 'bg-blue-50/60' : 'hover:bg-slate-50/50' }} transition-colors">
                            <td class="py-4 px-4 font-bold text-slate-800">Tahun ke-{{ $row->period }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600">{{ $row->year }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $formatCurrency($row->opening_value) }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $formatCurrency($row->expense) }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $formatCurrency($row->accumulated_depreciation) }}</td>
                            <td class="py-4 px-4 font-bold text-slate-800 whitespace-nowrap">{{ $formatCurrency($row->book_value) }}</td>
                            <td class="py-4 px-4">
                                @if($isSelected)
                                    <span class="inline-flex rounded-full bg-blue-100 text-blue-700 px-2.5 py-1 text-[10px] font-bold">Tahun yang ditampilkan</span>
                                @elseif($row->period === count($schedule))
                                    <span class="text-[10px] font-semibold text-slate-500">Akhir umur manfaat</span>
                                @else
                                    <span class="text-[10px] font-semibold text-slate-400">Proyeksi</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-[10px] leading-relaxed text-slate-400 border-t border-slate-100 pt-4">
            Jadwal ini merupakan proyeksi berdasarkan parameter penyusutan yang dipilih. Data snapshot tahunan tetap tersimpan hanya ketika Super Admin menjalankan perhitungan untuk tahun terkait.
        </p>
    </div>
</x-app-layout>
