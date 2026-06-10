<x-app-layout>
    @php
        $asset = $mutasi->jenis_aset === 'register' ? $mutasi->asetRegister : $mutasi->asetSmki;
        $assetName = $mutasi->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-');
        $assetCode = $mutasi->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-');
        $assetCategory = $mutasi->jenis_aset === 'register' ? ($asset->kode_barang ?? '-') : ($asset->jenis_barang ?? '-');
        $assetCondition = $mutasi->jenis_aset === 'register' ? ($asset->kondisi ?? '-') : ($asset->keadaan_barang ?? '-');
        $assetLocation = $mutasi->jenis_aset === 'register' ? ($asset->lokasi_aset ?? '-') : ($asset->ruangan ?? '-');
        $destinationLocation = $mutasi->bidangTujuan->nama_ruangan ?: ($mutasi->bidangTujuan->nama_bidang ?? '-');
        $statusClass = match ($mutasi->status) {
            'Disetujui' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Detail Riwayat Mutasi
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Detail perpindahan aset beserta metadata pengajuan dan verifikasinya.
            </p>
        </div>

        <a href="{{ route('riwayat-mutasi.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-[#EBF3FF] text-[#0F3092] border border-[#CBD5E1]">
                            {{ strtoupper($mutasi->jenis_aset) }}
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $statusClass }}">
                            {{ $mutasi->status }}
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">
                        {{ $assetName }}
                    </h3>
                    <p class="text-xs text-slate-400 font-semibold mt-1">
                        {{ $assetCode }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetCategory }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kondisi</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetCondition }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bidang Asal</span>
                    <span class="text-sm font-bold text-slate-700">{{ $mutasi->bidangAsal->nama_bidang ?? '-' }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bidang Tujuan</span>
                    <span class="text-sm font-bold text-[#0F3092]">{{ $mutasi->bidangTujuan->nama_bidang ?? '-' }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi Aset Saat Ini</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetLocation }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi Tujuan</span>
                    <span class="text-sm font-bold text-slate-700">{{ $destinationLocation }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Mutasi</span>
                    <span class="text-sm font-bold text-slate-700">
                        {{ $mutasi->tanggal_mutasi ? \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('d M Y') : '-' }}
                    </span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pengajuan</span>
                    <span class="text-sm font-bold text-slate-700">{{ optional($mutasi->created_at)->format('d M Y H:i') }}</span>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">
                    Alasan Mutasi
                </h4>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed min-h-20">
                    {{ $mutasi->alasan }}
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">
                    Metadata
                </h4>
                <div class="space-y-3 text-xs text-slate-600">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Diajukan Oleh</span>
                        <strong class="text-slate-800 text-right">{{ $mutasi->pemohon->nama ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Bidang Pemohon</span>
                        <strong class="text-slate-800 text-right">{{ $mutasi->pemohon->bidang->nama_bidang ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Diproses Oleh</span>
                        <strong class="text-slate-800 text-right">{{ $mutasi->penyetuju->nama ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Status</span>
                        <strong class="text-slate-800 text-right">{{ $mutasi->status }}</strong>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">
                    Catatan Histori
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Riwayat ini berasal dari data pengajuan mutasi dan keputusan verifikasi Super Admin.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
