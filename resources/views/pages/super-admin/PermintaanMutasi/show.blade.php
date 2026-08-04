<x-app-layout>
    @php
        $statusClass = match ($permintaan->status) {
            'Dipenuhi' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
        $mutasi = $permintaan->mutasiAset;
        $fulfilledAsset = $mutasi ? ($mutasi->jenis_aset === 'register' ? $mutasi->asetRegister : $mutasi->asetSmki) : null;
        $fulfilledAssetName = $fulfilledAsset ? ($mutasi->jenis_aset === 'register' ? $fulfilledAsset->nama_aset : $fulfilledAsset->merk_model) : '-';
        $fulfilledAssetCode = $fulfilledAsset ? ($mutasi->jenis_aset === 'register' ? $fulfilledAsset->kode_aset : $fulfilledAsset->nomor_kode_barang) : '-';
        $fulfilledAssetCategory = $fulfilledAsset ? ($mutasi->jenis_aset === 'register' ? $fulfilledAsset->kode_barang : $fulfilledAsset->jenis_barang) : '-';
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Detail Permintaan Mutasi</h2>
            <p class="text-sm text-slate-500 mt-1">Pilih aset yang sesuai untuk memenuhi permintaan bidang.</p>
        </div>
        <a href="{{ route('super-admin.permintaan-mutasi.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
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
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemohon</span>
                    <span class="text-sm font-bold text-slate-700">{{ $permintaan->peminta->nama ?? '-' }}</span>
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

            @if($permintaan->status === 'Menunggu Verifikasi')
                <div class="border-t border-slate-100 pt-6">
                    <div class="flex flex-col sm:flex-row justify-between gap-3 sm:items-end mb-4">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">Kandidat Aset</h4>
                            <p class="text-xs text-slate-400 mt-1">Aset terverifikasi dari bidang lain yang ditandai bisa dimutasi.</p>
                        </div>
                        <form action="{{ route('super-admin.permintaan-mutasi.show', $permintaan->id) }}" method="GET" class="relative w-full sm:w-80">
                            <input type="text" name="asset_search" value="{{ $assetSearch }}" placeholder="Cari kandidat aset..." class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </form>
                    </div>

                    <form action="{{ route('super-admin.permintaan-mutasi.fulfill', $permintaan->id) }}" method="POST" id="fulfill-request-form" class="space-y-5">
                        @csrf
                        @method('PATCH')

                        <div class="responsive-table rounded-xl border border-slate-200">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                                        <th class="py-3 px-4 w-10">Pilih</th>
                                        <th class="py-3 px-4">Aset</th>
                                        <th class="py-3 px-4">Bidang</th>
                                        <th class="py-3 px-4">Kategori</th>
                                        <th class="py-3 px-4">Kondisi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                                    @forelse($candidateAssets as $asset)
                                        @php
                                            $assetName = $permintaan->jenis_aset === 'register' ? $asset->nama_aset : $asset->merk_model;
                                            $assetCode = $permintaan->jenis_aset === 'register' ? $asset->kode_aset : $asset->nomor_kode_barang;
                                            $assetCategory = $permintaan->jenis_aset === 'register' ? $asset->kode_barang : $asset->jenis_barang;
                                            $assetCondition = $permintaan->jenis_aset === 'register' ? ($asset->kondisi ?? $asset->status_barang) : $asset->keadaan_barang;
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-3 px-4">
                                                <input type="radio" name="asset_choice" value="{{ $permintaan->jenis_aset }}:{{ $asset->id }}" class="h-4 w-4 text-[#0F3092] border-slate-300 focus:ring-[#0F3092]" required>
                                            </td>
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-slate-800">{{ $assetName }}</div>
                                                <div class="text-[10px] text-slate-400 font-semibold mt-1">{{ $assetCode }}</div>
                                            </td>
                                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $asset->bidang->nama_bidang ?? '-' }}</td>
                                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $assetCategory }}</td>
                                            <td class="py-3 px-4 font-semibold text-slate-600">{{ $assetCondition }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                                Belum ada kandidat aset yang cocok.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @error('asset_choice') <p class="text-red-500 text-[10px] font-semibold">{{ $message }}</p> @enderror

                        <div>
                            <label for="catatan_super_admin" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Catatan Super Admin</label>
                            <textarea id="catatan_super_admin" name="catatan_super_admin" rows="3" placeholder="Opsional, tuliskan catatan pemenuhan permintaan..." class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">{{ old('catatan_super_admin') }}</textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-3">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm">
                                Penuhi Permintaan
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">Keputusan</h4>
                @if($permintaan->status === 'Menunggu Verifikasi')
                    <form action="{{ route('super-admin.permintaan-mutasi.reject', $permintaan->id) }}" method="POST" id="reject-request-form" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="reject_note" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Catatan Penolakan</label>
                            <textarea id="reject_note" name="catatan_super_admin" rows="4" placeholder="Opsional, tuliskan alasan jika belum dapat dipenuhi..." class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                            Tolak Permintaan
                        </button>
                    </form>
                @else
                    <div class="space-y-3 text-xs text-slate-600">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <span>Status</span>
                            <strong class="text-slate-800 text-right">{{ $permintaan->status }}</strong>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <span>Diproses Oleh</span>
                            <strong class="text-slate-800 text-right">{{ $permintaan->pemroses->nama ?? '-' }}</strong>
                        </div>
                        <div>
                            <span class="block mb-1">Catatan</span>
                            <p class="font-semibold text-slate-700">{{ $permintaan->catatan_super_admin ?: '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if($mutasi)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">Mutasi Terbentuk</h4>
                            <p class="text-[11px] text-slate-400 font-medium mt-1">Permintaan ini sudah menjadi riwayat mutasi resmi.</p>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Terintegrasi
                        </span>
                    </div>

                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="text-sm font-extrabold text-slate-800">{{ $fulfilledAssetName }}</div>
                        <div class="text-[10px] text-slate-400 font-semibold mt-1">
                            {{ $fulfilledAssetCode }} <span class="px-1">|</span> {{ strtoupper($mutasi->jenis_aset) }} <span class="px-1">|</span> {{ $fulfilledAssetCategory }}
                        </div>
                    </div>

                    <div class="space-y-3 text-xs text-slate-600">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <span>Dari Bidang</span>
                            <strong class="text-slate-800 text-right">{{ $mutasi->bidangAsal->nama_bidang ?? '-' }}</strong>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <span>Ke Bidang</span>
                            <strong class="text-[#0F3092] text-right">{{ $mutasi->bidangTujuan->nama_bidang ?? '-' }}</strong>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <span>Tanggal Mutasi</span>
                            <strong class="text-slate-800 text-right">{{ $mutasi->tanggal_mutasi?->format('d M Y') }}</strong>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Riwayat Mutasi</span>
                            <a href="{{ route('riwayat-mutasi.show', $mutasi->id) }}" class="text-[#0F3092] font-bold hover:underline">Lihat</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
