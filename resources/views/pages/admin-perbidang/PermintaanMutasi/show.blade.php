<x-app-layout>
    @php
        $statusClass = match ($permintaan->status) {
            'Dipenuhi' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
        $mutasi = $permintaan->mutasiAset;
        $asset = $mutasi ? ($mutasi->jenis_aset === 'register' ? $mutasi->asetRegister : $mutasi->asetSmki) : null;
        $assetName = $asset ? ($mutasi->jenis_aset === 'register' ? $asset->nama_aset : $asset->merk_model) : '-';
        $assetCode = $asset ? ($mutasi->jenis_aset === 'register' ? $asset->kode_aset : $asset->nomor_kode_barang) : '-';
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Detail Permintaan Mutasi</h2>
            <p class="text-sm text-slate-500 mt-1">Informasi kebutuhan aset dan keputusan Super Admin.</p>
        </div>
        <a href="{{ route('admin-perbidang.permintaan-mutasi.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-[#EBF3FF] text-[#0F3092] border border-[#CBD5E1]">{{ strtoupper($permintaan->jenis_aset) }}</span>
                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $statusClass }}">{{ $permintaan->status }}</span>
                </div>
                <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">{{ $permintaan->nama_kebutuhan }}</h3>
                <p class="text-xs text-slate-400 font-semibold mt-1">{{ $permintaan->kategori_aset }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bidang Peminta</span>
                    <span class="text-sm font-bold text-slate-700">{{ $permintaan->bidangPeminta->nama_bidang ?? '-' }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi Penggunaan</span>
                    <span class="text-sm font-bold text-slate-700">{{ $permintaan->lokasi_penggunaan }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Permintaan</span>
                    <span class="text-sm font-bold text-slate-700">{{ $permintaan->tanggal_permintaan?->format('d M Y') }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Diproses Oleh</span>
                    <span class="text-sm font-bold text-slate-700">{{ $permintaan->pemroses->nama ?? '-' }}</span>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">Spesifikasi</h4>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed">{{ $permintaan->spesifikasi ?: '-' }}</div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">Alasan Permintaan</h4>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed">{{ $permintaan->alasan }}</div>
            </div>

            @if($permintaan->catatan_super_admin)
                <div>
                    <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">Catatan Super Admin</h4>
                    <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed">{{ $permintaan->catatan_super_admin }}</div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">Aset Yang Dipenuhi</h4>
                @if($mutasi)
                    <div>
                        <div class="text-sm font-extrabold text-slate-800">{{ $assetName }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold mt-1">{{ $assetCode }}</div>
                    </div>
                    <div class="space-y-3 text-xs text-slate-600">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <span>Dari Bidang</span>
                            <strong class="text-slate-800 text-right">{{ $mutasi->bidangAsal->nama_bidang ?? '-' }}</strong>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Riwayat Mutasi</span>
                            <a href="{{ route('riwayat-mutasi.show', $mutasi->id) }}" class="text-[#0F3092] font-bold hover:underline">Lihat</a>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-500 leading-relaxed">Belum ada aset yang dipilih oleh Super Admin untuk permintaan ini.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
