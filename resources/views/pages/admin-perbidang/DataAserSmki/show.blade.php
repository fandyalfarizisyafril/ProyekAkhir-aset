<x-app-layout>
    @php
        $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y H:i') : '-';
        $formatDateOnly = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '-';
        $displayStatus = fn ($status) => $status === 'Aktif' || blank($status) ? 'Tersedia' : $status;
        $verificationClass = match ($asset->status_verifikasi) {
            'Terverifikasi' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
        $assetStatus = $asset->status_verifikasi === 'Ditolak' ? 'Ditolak' : $displayStatus($asset->status);
        $assetStatusClass = match ($assetStatus) {
            'Dipinjam' => 'bg-sky-50 text-sky-700 border border-sky-200',
            'Maintenance' => 'bg-amber-50 text-amber-700 border border-amber-200',
            'Rusak', 'Dihapus', 'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
        };
        $identityRows = [
            'Nomor Kode Barang' => $asset->nomor_kode_barang,
            'Jenis Barang' => $asset->jenis_barang,
            'Merk / Model' => $asset->merk_model,
            'Nomor Seri' => $asset->no_ser_model ?: '-',
            'Bidang' => $asset->bidang->nama_bidang ?? '-',
            'Ruangan' => $asset->ruangan ?: '-',
            'Penanggung Jawab' => $asset->penanggung_jawab ?: '-',
        ];
        $specRows = [
            'Ukuran' => $asset->ukuran ?: '-',
            'Bahan' => $asset->bahan ?: '-',
            'Tahun Pembelian' => $asset->tahun_pembuatan ?: '-',
            'Jumlah' => trim(($asset->jumlah ?? '-') . ' ' . ($asset->satuan ?? '')),
            'Keadaan Barang' => $asset->keadaan_barang ?: '-',
            'Status Aset' => $assetStatus,
        ];
        $metadataRows = [
            'Diinput Oleh' => $asset->inputter->nama ?? $asset->inputter->name ?? '-',
            'Dibuat Pada' => $formatDate($asset->created_at),
            'Diverifikasi Oleh' => $asset->verifier->nama ?? $asset->verifier->name ?? '-',
            'Pembaruan Terakhir' => $formatDate($asset->updated_at),
        ];
        $conditionHistories = $asset->riwayatKondisi->sortByDesc('created_at')->values();
        $latestConditionHistory = $conditionHistories->first();
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Detail Aset SMKI
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Tampilan read-only informasi aset dan riwayat terkait.
            </p>
        </div>

        <a href="{{ route('admin-perbidang.data-aset-smki.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                            SMKI
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $verificationClass }}">
                            {{ $asset->status_verifikasi }}
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $assetStatusClass }}">
                            {{ $assetStatus }}
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">
                        {{ $asset->merk_model }}
                    </h3>
                    <p class="text-xs text-slate-400 font-semibold mt-1">
                        {{ $asset->nomor_kode_barang }} | {{ $asset->jenis_barang }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Jumlah</span>
                        <strong class="block text-sm text-slate-800 mt-1">{{ $asset->jumlah }} {{ $asset->satuan }}</strong>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Kondisi</span>
                        <strong class="block text-sm text-slate-800 mt-1">{{ $asset->keadaan_barang ?: '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <x-readonly-detail-card title="Identitas Aset" :rows="$identityRows" />
                <x-readonly-detail-card title="Spesifikasi dan Status" :rows="$specRows" />

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">Keterangan</h3>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 leading-relaxed">
                        {{ $asset->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <x-readonly-detail-card title="Metadata" :rows="$metadataRows" />

                <x-asset-qr-card :asset="$asset" type="smki" />
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div x-data="{ openConditionHistory: false }">
            <x-asset-history-card title="Riwayat Kondisi" empty="Belum ada riwayat kondisi." :has-items="$conditionHistories->isNotEmpty()">
                @if($latestConditionHistory)
                    @php($history = $latestConditionHistory)
                    <div class="rounded-xl border border-slate-100 p-4">
                        <div class="flex justify-between gap-3">
                            <div class="font-bold text-slate-800 text-sm">{{ $history->keadaan_lama }} ke {{ $history->keadaan_baru }}</div>
                            <span class="text-[10px] font-semibold text-slate-400">{{ $formatDate($history->created_at) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $history->catatan ?: 'Tanpa catatan.' }}</p>
                        @if($history->foto_path)
                            <a href="{{ asset('storage/' . $history->foto_path) }}" target="_blank" class="mt-3 block overflow-hidden rounded-xl border border-slate-200 bg-slate-50 hover:border-[#0F3092] transition-colors">
                                <img src="{{ asset('storage/' . $history->foto_path) }}" alt="Foto kondisi {{ $asset->merk_model }}" class="h-36 w-full object-cover">
                                <span class="block px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-[#0F3092]">
                                    Lihat Foto Kondisi
                                </span>
                            </a>
                        @endif
                        <p class="text-[10px] text-slate-400 mt-2">Oleh {{ $history->updater->nama ?? $history->updater->name ?? '-' }}</p>
                    </div>

                    @if($conditionHistories->count() > 1)
                        <button type="button" @click="openConditionHistory = true" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-[11px] font-extrabold uppercase tracking-wider text-[#0F3092] hover:border-[#0F3092] hover:bg-blue-50 transition-colors">
                            Lihat Semua Riwayat Kondisi ({{ $conditionHistories->count() }})
                        </button>
                    @endif
                @endif
            </x-asset-history-card>

            <div x-show="openConditionHistory" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4 py-6" role="dialog" aria-modal="true" @keydown.escape.window="openConditionHistory = false">
                <div class="absolute inset-0" @click="openConditionHistory = false"></div>
                <div x-show="openConditionHistory" x-transition class="relative z-10 w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-xl">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Semua Riwayat Kondisi</h3>
                            <p class="text-xs text-slate-400 mt-1">{{ $asset->merk_model }} | {{ $asset->nomor_kode_barang }}</p>
                        </div>
                        <button type="button" @click="openConditionHistory = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50" aria-label="Tutup riwayat kondisi">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto p-5">
                        <div class="space-y-3">
                            @foreach($conditionHistories as $history)
                                <div class="rounded-xl border border-slate-100 p-4">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm">{{ $history->keadaan_lama }} ke {{ $history->keadaan_baru }}</div>
                                            <p class="text-xs text-slate-500 mt-1">{{ $history->catatan ?: 'Tanpa catatan.' }}</p>
                                        </div>
                                        <span class="text-[10px] font-semibold text-slate-400 whitespace-nowrap">{{ $formatDate($history->created_at) }}</span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-[10px] text-slate-400">
                                        <span>Oleh {{ $history->updater->nama ?? $history->updater->name ?? '-' }}</span>
                                        @if($history->foto_path)
                                            <a href="{{ asset('storage/' . $history->foto_path) }}" target="_blank" class="inline-flex rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-bold uppercase tracking-wider text-[#0F3092] hover:border-[#0F3092] transition-colors">
                                                Lihat Foto Kondisi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            </div>

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
