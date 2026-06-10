<x-app-layout>
    @php
        $asset = $peminjaman->jenis_aset === 'register' ? $peminjaman->asetRegister : $peminjaman->asetSmki;
        $assetName = $peminjaman->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-');
        $assetCode = $peminjaman->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-');
        $assetCategory = $peminjaman->jenis_aset === 'register' ? ($asset->kode_barang ?? '-') : ($asset->jenis_barang ?? '-');
        $assetCondition = $peminjaman->jenis_aset === 'register' ? ($asset->kondisi ?? '-') : ($asset->keadaan_barang ?? '-');
        $assetLocation = $peminjaman->jenis_aset === 'register' ? ($asset->lokasi_aset ?? '-') : ($asset->ruangan ?? '-');
        $assetStatus = $asset->status ?? 'Tersedia';
        $sourceBidangName = $peminjaman->bidangAsal->nama_bidang ?? $asset->bidang->nama_bidang ?? '-';
        $borrowerName = $peminjaman->nama_peminjam ?: ($peminjaman->peminjam->nama ?? '-');
        $statusClass = match ($peminjaman->status) {
            'Disetujui' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
            'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
            default => 'bg-amber-50 text-amber-700 border border-amber-200',
        };
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Detail Verifikasi Peminjaman
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Pastikan aset tersedia sebelum peminjaman disetujui.
            </p>
        </div>

        <a href="{{ route('super-admin.verifikasi-peminjaman.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
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

                @if($peminjaman->status === 'Menunggu Verifikasi')
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <form action="{{ route('super-admin.verifikasi-peminjaman.approve', $peminjaman->id) }}" method="POST" class="verification-form flex-1 sm:flex-none" data-action="menyetujui" data-asset-name="{{ $assetName }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                                Setujui
                            </button>
                        </form>
                        <form action="{{ route('super-admin.verifikasi-peminjaman.reject', $peminjaman->id) }}" method="POST" class="verification-form flex-1 sm:flex-none" data-action="menolak" data-asset-name="{{ $assetName }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                                Tolak
                            </button>
                        </form>
                    </div>
                @endif
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
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi Asal Aset</span>
                    <span class="text-sm font-bold text-slate-700">{{ $sourceBidangName }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status Aset Saat Ini</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetStatus }}</span>
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
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pengajuan</span>
                    <span class="text-sm font-bold text-slate-700">{{ optional($peminjaman->created_at)->format('d M Y H:i') }}</span>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">
                    Keperluan
                </h4>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed min-h-20">
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
                    Metadata Pengajuan
                </h4>
                <div class="space-y-3 text-xs text-slate-600">
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Peminjam</span>
                        <strong class="text-slate-800 text-right">{{ $borrowerName }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Pengaju</span>
                        <strong class="text-slate-800 text-right">{{ $peminjaman->peminjam->bidang->nama_bidang ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Diproses Oleh</span>
                        <strong class="text-slate-800 text-right">{{ $peminjaman->penyetuju->nama ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Status</span>
                        <strong class="text-slate-800 text-right">{{ $peminjaman->status }}</strong>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">
                    Dampak Persetujuan
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Jika disetujui, status aset akan berubah menjadi Dipinjam sampai proses pengembalian dilakukan.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.verification-form').forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const action = this.getAttribute('data-action');
                    const assetName = this.getAttribute('data-asset-name');

                    Swal.fire({
                        title: 'Konfirmasi Peminjaman',
                        text: `Apakah Anda yakin ingin ${action} peminjaman aset "${assetName}"?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#002D84',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, lanjutkan',
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
