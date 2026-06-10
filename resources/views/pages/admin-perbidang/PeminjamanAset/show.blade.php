<x-app-layout>
    @php
        $asset = $peminjaman->jenis_aset === 'register' ? $peminjaman->asetRegister : $peminjaman->asetSmki;
        $assetName = $peminjaman->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-');
        $assetCode = $peminjaman->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-');
        $assetCategory = $peminjaman->jenis_aset === 'register' ? ($asset->kode_barang ?? '-') : ($asset->jenis_barang ?? '-');
        $assetCondition = $peminjaman->jenis_aset === 'register' ? ($asset->kondisi ?? '-') : ($asset->keadaan_barang ?? '-');
        $assetLocation = $peminjaman->jenis_aset === 'register' ? ($asset->lokasi_aset ?? '-') : ($asset->ruangan ?? '-');
        $statusClass = match ($peminjaman->status) {
            'Disetujui' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Dikembalikan' => 'bg-blue-50 text-blue-700 border border-blue-200',
            'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Detail Peminjaman Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Informasi lengkap pengajuan peminjaman aset.
            </p>
        </div>

        <a href="{{ route('admin-perbidang.peminjaman-aset.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-emerald-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center space-x-3 text-rose-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-[#EBF3FF] text-[#0F3092] border border-[#CBD5E1]">
                            {{ strtoupper($peminjaman->jenis_aset) }}
                        </span>
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $statusClass }}">
                            {{ $peminjaman->status }}
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
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bidang Aset</span>
                    <span class="text-sm font-bold text-slate-700">{{ $asset->bidang->nama_bidang ?? '-' }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi Aset</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetLocation }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pinjam</span>
                    <span class="text-sm font-bold text-slate-700">{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->format('d M Y') }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Rencana Kembali</span>
                    <span class="text-sm font-bold text-slate-700">{{ \Carbon\Carbon::parse($peminjaman->tanggal_rencana_kembali)->format('d M Y') }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Kembali</span>
                    <span class="text-sm font-bold text-slate-700">
                        {{ $peminjaman->tanggal_kembali ? \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->format('d M Y') : '-' }}
                    </span>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">
                    Keperluan
                </h4>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed">
                    {{ $peminjaman->keperluan }}
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">
                    Catatan
                </h4>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed">
                    {{ $peminjaman->catatan ?: 'Tidak ada catatan tambahan.' }}
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">
                    Metadata Verifikasi
                </h4>
                <div class="space-y-3 text-xs text-slate-600">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Peminjam</span>
                        <strong class="text-slate-800 text-right">{{ $peminjaman->peminjam->nama ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Bidang Peminjam</span>
                        <strong class="text-slate-800 text-right">{{ $peminjaman->peminjam->bidang->nama_bidang ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Disetujui Oleh</span>
                        <strong class="text-slate-800 text-right">{{ $peminjaman->penyetuju->nama ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Status</span>
                        <strong class="text-slate-800 text-right">{{ $peminjaman->status }}</strong>
                    </div>
                </div>
            </div>

            @if($peminjaman->status === 'Disetujui' && is_null($peminjaman->tanggal_kembali))
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
                    <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">
                        Catat Pengembalian
                    </h4>

                    <form action="{{ route('admin-perbidang.peminjaman-aset.return', $peminjaman->id) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="tanggal_kembali" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Tanggal Kembali
                            </label>
                            <input
                                type="date"
                                id="tanggal_kembali"
                                name="tanggal_kembali"
                                value="{{ old('tanggal_kembali', now()->toDateString()) }}"
                                min="{{ \Carbon\Carbon::parse($peminjaman->tanggal_pinjam)->toDateString() }}"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 focus:outline-none focus:border-[#0F3092]"
                                required
                            >
                            @error('tanggal_kembali')
                                <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="catatan_pengembalian" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">
                                Catatan Pengembalian
                            </label>
                            <textarea
                                id="catatan_pengembalian"
                                name="catatan_pengembalian"
                                rows="3"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-xs font-semibold text-slate-700 focus:outline-none focus:border-[#0F3092]"
                                placeholder="Kondisi aset saat dikembalikan"
                            >{{ old('catatan_pengembalian') }}</textarea>
                            @error('catatan_pengembalian')
                                <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                            Simpan Pengembalian
                        </button>
                    </form>
                </div>
            @endif

            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">
                    Catatan
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    @if($peminjaman->status === 'Dikembalikan')
                        Peminjaman selesai. Status aset sudah kembali menjadi Tersedia.
                    @elseif($peminjaman->status === 'Disetujui')
                        Peminjaman aktif. Catat pengembalian setelah aset diterima kembali.
                    @else
                        Pengajuan ini akan mengubah status aset menjadi Dipinjam setelah disetujui Super Admin pada tahap verifikasi peminjaman.
                    @endif
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
