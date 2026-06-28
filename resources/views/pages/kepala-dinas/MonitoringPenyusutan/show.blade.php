<x-app-layout>
    @php
        $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $latestBookValue = $latestDepreciation?->nilai_akhir_tahun ?? $asset->nilai;
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Detail Penyusutan Aset</h2>
            <p class="text-sm text-slate-500 mt-1">Riwayat perhitungan penyusutan dalam tampilan read-only.</p>
        </div>
        <a href="{{ route('kepala-dinas.monitoring-aset.penyusutan', $backFilters) }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-colors">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="min-w-0">
                <span class="inline-flex px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-blue-50 text-blue-700 border border-blue-200">REGISTER</span>
                <h3 class="text-xl font-extrabold text-slate-800 mt-3">{{ $asset->nama_aset }}</h3>
                <p class="text-xs text-slate-400 font-semibold mt-1">{{ $asset->kode_aset }} | {{ $asset->kode_barang }}</p>
                <p class="text-xs text-slate-500 mt-2">{{ $asset->bidang->nama_bidang ?? '-' }}</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto">
                <div class="min-w-[160px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nilai Perolehan</span>
                    <strong class="block text-sm text-slate-800 mt-1 whitespace-nowrap">{{ $formatCurrency($asset->nilai) }}</strong>
                </div>
                <div class="min-w-[160px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nilai Buku Terakhir</span>
                    <strong class="block text-sm text-slate-800 mt-1 whitespace-nowrap">{{ $formatCurrency($latestBookValue) }}</strong>
                </div>
                <div class="min-w-[130px] rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Riwayat</span>
                    <strong class="block text-sm text-slate-800 mt-1">{{ $history->total() }} Tahun</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 space-y-5">
        <div>
            <h3 class="text-base font-bold text-slate-800">Riwayat Penyusutan Per Tahun</h3>
            <p class="text-xs text-slate-400 mt-1">Setiap baris merupakan hasil perhitungan yang disimpan oleh Super Admin.</p>
        </div>

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Tahun</th>
                        <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Nilai Awal</th>
                        <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Beban</th>
                        <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Nilai Buku</th>
                        <th class="py-4 px-4 min-w-[120px] whitespace-nowrap">Nilai Residu</th>
                        <th class="py-4 px-4">Umur Manfaat</th>
                        <th class="py-4 px-4">Metode</th>
                        <th class="py-4 px-4 min-w-[180px]">Dihitung Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($history as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4 font-bold text-slate-800">{{ $item->tahun }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $formatCurrency($item->nilai_awal_tahun) }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $formatCurrency($item->beban_penyusutan) }}</td>
                            <td class="py-4 px-4 font-bold text-slate-800 whitespace-nowrap">{{ $formatCurrency($item->nilai_akhir_tahun) }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $formatCurrency($item->nilai_residu) }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600 whitespace-nowrap">{{ $item->umur_manfaat_tahun }} tahun</td>
                            <td class="py-4 px-4 font-semibold text-slate-600">{{ $item->metode }}</td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-600">{{ $item->calculator->nama ?? $item->calculator->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">{{ $item->tanggal_hitung?->format('d M Y H:i') ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Belum ada perhitungan penyusutan untuk aset ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
            <div class="border-t border-slate-100 pt-4">{{ $history->links() }}</div>
        @endif
    </div>
</x-app-layout>
