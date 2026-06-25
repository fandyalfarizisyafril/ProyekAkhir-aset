<x-app-layout>
    @php
        $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y H:i') : '-';
        $formatDateOnly = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '-';
        $formatCurrency = fn ($value) => $value === null ? '-' : 'Rp ' . number_format((float) $value, 0, ',', '.');
        $displayStatus = fn ($status) => $status === 'Aktif' || blank($status) ? 'Tersedia' : $status;
        $verificationClass = match ($asset->status_verifikasi) {
            'Terverifikasi' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
        $assetStatus = $displayStatus($asset->status);
        $assetStatusClass = match ($assetStatus) {
            'Dipinjam' => 'bg-sky-50 text-sky-700 border border-sky-200',
            'Maintenance' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'Rusak', 'Dihapus' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        };
        $identityRows = [
            'Kode Aset' => $asset->kode_aset,
            'Kode Barang' => $asset->kode_barang,
            'Kode Urut Barang' => $asset->kode_urut_barang,
            'Nama Aset' => $asset->nama_aset,
            'Pemilik Aset' => $asset->pemilik_aset,
            'Pengguna' => $asset->pengguna ?: '-',
            'Bidang' => $asset->bidang->nama_bidang ?? '-',
            'Lokasi Aset' => $asset->lokasi_aset ?: '-',
        ];
        $classificationRows = [
            'Kondisi' => $asset->kondisi ?: $asset->status_barang,
            'Status Barang' => $asset->status_barang ?: '-',
            'Status Aset' => $assetStatus,
            'Kerahasiaan' => $asset->kerahasiaan ?: '-',
            'Kritikalitas' => $asset->kritikalitas ?: '-',
            'Nilai Perolehan' => $formatCurrency($asset->nilai),
            'Metode Pemusnahan' => $asset->metode_pemusnahan ?: '-',
        ];
        $metadataRows = [
            'Diinput Oleh' => $asset->inputter->nama ?? $asset->inputter->name ?? '-',
            'Dibuat Pada' => $formatDate($asset->created_at),
            'Diverifikasi Oleh' => $asset->verifier->nama ?? $asset->verifier->name ?? '-',
            'Pembaruan Terakhir' => $formatDate($asset->updated_at),
        ];
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Detail Aset Register
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Tampilan read-only informasi aset dan riwayat terkait.
            </p>
        </div>

        <a href="{{ route('admin-perbidang.data-aset-register.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-[#EBF3FF] text-[#0F3092] border border-[#CBD5E1]">
                            REGISTER
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $verificationClass }}">
                            {{ $asset->status_verifikasi }}
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $assetStatusClass }}">
                            {{ $assetStatus }}
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">
                        {{ $asset->nama_aset }}
                    </h3>
                    <p class="text-xs text-slate-400 font-semibold mt-1">
                        {{ $asset->kode_aset }} | {{ $asset->kode_barang }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Nilai</span>
                        <strong class="block text-sm text-slate-800 mt-1">{{ $formatCurrency($asset->nilai) }}</strong>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Kondisi</span>
                        <strong class="block text-sm text-slate-800 mt-1">{{ $asset->kondisi ?: '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <x-readonly-detail-card title="Identitas Aset" :rows="$identityRows" />
                <x-readonly-detail-card title="Klasifikasi dan Nilai" :rows="$classificationRows" />

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">Keterangan</h3>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 leading-relaxed">
                        {{ $asset->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <x-readonly-detail-card title="Metadata" :rows="$metadataRows" />

                <x-asset-qr-card :asset="$asset" type="register" />
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <x-asset-history-card title="Riwayat Kondisi" empty="Belum ada riwayat kondisi." :has-items="$asset->riwayatKondisi->isNotEmpty()">
                @foreach($asset->riwayatKondisi->sortByDesc('created_at')->take(5) as $history)
                    <div class="rounded-xl border border-slate-100 p-4">
                        <div class="flex justify-between gap-3">
                            <div class="font-bold text-slate-800 text-sm">{{ $history->keadaan_lama }} ke {{ $history->keadaan_baru }}</div>
                            <span class="text-[10px] font-semibold text-slate-400">{{ $formatDate($history->created_at) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $history->catatan ?: 'Tanpa catatan.' }}</p>
                        <p class="text-[10px] text-slate-400 mt-2">Oleh {{ $history->updater->nama ?? $history->updater->name ?? '-' }}</p>
                    </div>
                @endforeach
            </x-asset-history-card>

            <x-asset-history-card title="Riwayat Mutasi" empty="Belum ada riwayat mutasi." :has-items="$asset->mutasi->isNotEmpty()">
                @foreach($asset->mutasi->sortByDesc('created_at')->take(5) as $mutation)
                    <div class="rounded-xl border border-slate-100 p-4">
                        <div class="font-bold text-slate-800 text-sm">{{ $mutation->bidangAsal->nama_bidang ?? '-' }} ke {{ $mutation->bidangTujuan->nama_bidang ?? '-' }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $formatDateOnly($mutation->tanggal_mutasi) }} | {{ $mutation->status }}</div>
                        <p class="text-[10px] text-slate-400 mt-2">{{ $mutation->alasan }}</p>
                    </div>
                @endforeach
            </x-asset-history-card>

            <x-asset-history-card title="Riwayat Peminjaman" empty="Belum ada riwayat peminjaman." :has-items="$asset->peminjaman->isNotEmpty()">
                @foreach($asset->peminjaman->sortByDesc('created_at')->take(5) as $loan)
                    <div class="rounded-xl border border-slate-100 p-4">
                        <div class="font-bold text-slate-800 text-sm">{{ $loan->nama_peminjam ?: ($loan->peminjam->nama ?? '-') }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $formatDateOnly($loan->tanggal_pinjam) }} sampai {{ $formatDateOnly($loan->tanggal_rencana_kembali) }}</div>
                        <p class="text-[10px] text-slate-400 mt-2">{{ $loan->status }} | {{ $loan->keperluan }}</p>
                    </div>
                @endforeach
            </x-asset-history-card>
        </div>
    </div>
</x-app-layout>
