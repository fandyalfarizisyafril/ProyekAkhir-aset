<x-app-layout>
    @php
        $formatCurrency = fn ($value) => $value === null ? '-' : 'Rp ' . number_format((float) $value, 0, ',', '.');
        $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y H:i') : '-';
        $displayStatus = fn ($status) => $status === 'Aktif' || blank($status) ? 'Tersedia' : $status;
        $isRegister = $type === 'register';
        $assetName = $isRegister ? $asset->nama_aset : $asset->merk_model;
        $assetCode = $isRegister ? $asset->kode_aset : $asset->nomor_kode_barang;
        $assetCategory = $isRegister ? $asset->kode_barang : $asset->jenis_barang;
        $assetCondition = $isRegister ? ($asset->kondisi ?: $asset->status_barang) : $asset->keadaan_barang;
        $assetLocation = $isRegister ? $asset->lokasi_aset : $asset->ruangan;
        $assetStatus = $displayStatus($asset->status);
        $identityRows = $isRegister
            ? [
                'Kode Aset' => $asset->kode_aset,
                'Nama Aset' => $asset->nama_aset,
                'Kategori' => $asset->kode_barang,
                'Kode Urut Barang' => $asset->kode_urut_barang,
                'Bidang' => $asset->bidang->nama_bidang ?? '-',
                'Lokasi' => $asset->lokasi_aset ?: '-',
                'Pengguna' => $asset->pengguna ?: '-',
                'Pemilik Aset' => $asset->pemilik_aset ?: '-',
            ]
            : [
                'Nomor Kode Barang' => $asset->nomor_kode_barang,
                'Merk / Model' => $asset->merk_model,
                'Jenis Barang' => $asset->jenis_barang,
                'Nomor Seri' => $asset->no_ser_model ?: '-',
                'Bidang' => $asset->bidang->nama_bidang ?? '-',
                'Ruangan' => $asset->ruangan ?: '-',
                'Penanggung Jawab' => $asset->penanggung_jawab ?: '-',
                'Jumlah' => trim(($asset->jumlah ?? '-') . ' ' . ($asset->satuan ?? '')),
            ];
        $statusRows = $isRegister
            ? [
                'Kondisi' => $assetCondition ?: '-',
                'Status Aset' => $assetStatus,
                'Status Verifikasi' => $asset->status_verifikasi,
                'Kerahasiaan' => $asset->kerahasiaan ?: '-',
                'Kritikalitas' => $asset->kritikalitas ?: '-',
                'Nilai Perolehan' => $formatCurrency($asset->nilai),
            ]
            : [
                'Kondisi' => $assetCondition ?: '-',
                'Status Aset' => $assetStatus,
                'Status Verifikasi' => $asset->status_verifikasi,
                'Ukuran' => $asset->ukuran ?: '-',
                'Bahan' => $asset->bahan ?: '-',
                'Tahun Pembuatan' => $asset->tahun_pembuatan ?: '-',
            ];
        $metadataRows = [
            'Diinput Oleh' => $asset->inputter->nama ?? $asset->inputter->name ?? '-',
            'Diverifikasi Oleh' => $asset->verifier->nama ?? $asset->verifier->name ?? '-',
            'Tanggal Input' => $formatDate($asset->created_at),
            'Pembaruan Terakhir' => $formatDate($asset->updated_at),
        ];
        $deletionRows = ($from ?? 'data') === 'nonaktif'
            ? [
                'Tanggal Nonaktif' => $deletion?->tanggal_penghapusan?->format('d M Y') ?? '-',
                'Metode Penghapusan' => $deletion?->metode_penghapusan ?? '-',
                'Nilai Buku' => $formatCurrency($deletion?->nilai_buku),
                'Status Sebelum' => $deletion?->status_sebelum ?? '-',
                'Dinonaktifkan Oleh' => $deletion?->remover?->nama ?? $deletion?->remover?->name ?? '-',
                'Alasan' => $deletion?->alasan ?? '-',
            ]
            : [];
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Detail Monitoring Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Tampilan read-only untuk pemantauan aset oleh Kepala Dinas.
            </p>
        </div>
        <a href="{{ route($backRoute) }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md {{ $isRegister ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                            {{ $isRegister ? 'REGISTER' : 'SMKI' }}
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $asset->status_verifikasi }}
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-50 text-slate-600 border border-slate-200">
                            {{ $assetStatus }}
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">
                        {{ $assetName }}
                    </h3>
                    <p class="text-xs text-slate-400 font-semibold mt-1">
                        {{ $assetCode }} | {{ $assetCategory }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 w-full lg:w-auto">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Kondisi</span>
                        <strong class="block text-sm text-slate-800 mt-1">{{ $assetCondition ?: '-' }}</strong>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Lokasi</span>
                        <strong class="block text-sm text-slate-800 mt-1">{{ $assetLocation ?: '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <x-readonly-detail-card title="Identitas Aset" :rows="$identityRows" />
                <x-readonly-detail-card title="Kondisi dan Status" :rows="$statusRows" />
                @if(($from ?? 'data') === 'nonaktif')
                    <x-readonly-detail-card title="Informasi Nonaktif" :rows="$deletionRows" />
                @endif
            </div>

            <div class="space-y-6">
                <x-readonly-detail-card title="Metadata" :rows="$metadataRows" />

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-4">QR Aset</h3>
                    <a href="{{ route('qr.asset.show', [$type, $asset->id]) }}" target="_blank" class="inline-flex w-full items-center justify-center bg-[#0F3092] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-4 py-3 rounded-xl transition-colors">
                        Lihat Detail QR
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
