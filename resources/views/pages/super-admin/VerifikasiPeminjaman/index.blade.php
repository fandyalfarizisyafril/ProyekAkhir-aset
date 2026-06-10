<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Verifikasi Peminjaman Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Tinjau pengajuan peminjaman dan ubah status aset menjadi Dipinjam saat disetujui.
            </p>
        </div>
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-dashboard.stats-card title="Menunggu" value="{{ number_format($pendingCount) }}" trend="Butuh keputusan" type="info" />
        <x-dashboard.stats-card title="Disetujui" value="{{ number_format($approvedCount) }}" trend="Aset dipinjam" type="success" />
        <x-dashboard.stats-card title="Ditolak" value="{{ number_format($rejectedCount) }}" trend="Tidak dipinjam" type="danger" />
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <form action="{{ route('super-admin.verifikasi-peminjaman.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <select name="jenis" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>Register</option>
                    <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Status" {{ $filters['status'] === 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Menunggu Verifikasi" {{ $filters['status'] === 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="Disetujui" {{ $filters['status'] === 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Ditolak" {{ $filters['status'] === 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <select name="bidang_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                    @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>
                            {{ $bidang->nama_bidang }}
                        </option>
                    @endforeach
                </select>

                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Cari aset, kode, peminjam..."
                        class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#0F3092]" title="Cari">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>

            @if($filters['search'] || $filters['jenis'] !== 'Semua Jenis' || $filters['status'] !== 'Menunggu Verifikasi' || $filters['bidang_id'] !== 'Semua Bidang')
                <a href="{{ route('super-admin.verifikasi-peminjaman.index') }}" class="inline-block text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold hover:underline">
                    Reset Filter
                </a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Aset</th>
                        <th class="py-4 px-4">Peminjam</th>
                        <th class="py-4 px-4">Tanggal</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($peminjaman as $item)
                        @php
                            $asset = $item->jenis_aset === 'register' ? $item->asetRegister : $item->asetSmki;
                            $assetName = $item->jenis_aset === 'register' ? ($asset->nama_aset ?? '-') : ($asset->merk_model ?? '-');
                            $assetCode = $item->jenis_aset === 'register' ? ($asset->kode_aset ?? '-') : ($asset->nomor_kode_barang ?? '-');
                            $statusClass = match ($item->status) {
                                'Disetujui' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                default => 'bg-amber-50 text-amber-700 border border-amber-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold tracking-wider rounded-md bg-[#EBF3FF] text-[#0F3092] border border-[#CBD5E1]">
                                        {{ strtoupper($item->jenis_aset) }}
                                    </span>
                                    <span class="font-bold text-slate-800 text-sm">{{ $assetName }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-semibold">{{ $assetCode }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-700">{{ $item->peminjam->nama ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">{{ $item->peminjam->bidang->nama_bidang ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ \Carbon\Carbon::parse($item->tanggal_pinjam)->format('d M Y') }}
                                <div class="text-[10px] text-slate-400 mt-1">
                                    kembali {{ \Carbon\Carbon::parse($item->tanggal_rencana_kembali)->format('d M Y') }}
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 {{ $statusClass }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('super-admin.verifikasi-peminjaman.show', $item->id) }}" class="text-slate-500 hover:text-slate-700 transition-colors p-1 hover:bg-slate-100 rounded" title="Detail Peminjaman">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if($item->status === 'Menunggu Verifikasi')
                                        <form action="{{ route('super-admin.verifikasi-peminjaman.approve', $item->id) }}" method="POST" class="inline verification-form" data-action="menyetujui" data-asset-name="{{ $assetName }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-700 transition-colors p-1 hover:bg-emerald-50 rounded" title="Setujui Peminjaman">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('super-admin.verifikasi-peminjaman.reject', $item->id) }}" method="POST" class="inline verification-form" data-action="menolak" data-asset-name="{{ $assetName }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-red-600 hover:text-red-700 transition-colors p-1 hover:bg-red-50 rounded" title="Tolak Peminjaman">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada pengajuan peminjaman yang cocok dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($peminjaman->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $peminjaman->firstItem() ?? 0 }}-{{ $peminjaman->lastItem() ?? 0 }} dari {{ $peminjaman->total() }} pengajuan
                </div>
                <div>
                    {{ $peminjaman->links() }}
                </div>
            </div>
        @endif
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
