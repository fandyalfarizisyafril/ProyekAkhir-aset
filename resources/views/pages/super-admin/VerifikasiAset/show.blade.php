<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Detail Verifikasi Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Tinjau kelengkapan data sebelum aset diverifikasi atau ditolak.
            </p>
        </div>

        <a href="{{ route('super-admin.verifikasi-aset.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
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
                            {{ $assetData->type_label }}
                        </span>
                        @php
                            $statusClass = match ($assetData->status_verifikasi) {
                                'Terverifikasi' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                'Ditolak' => 'bg-rose-50 text-rose-700 border border-rose-200',
                                default => 'bg-amber-50 text-amber-700 border border-amber-200',
                            };
                        @endphp
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full {{ $statusClass }}">
                            {{ $assetData->status_verifikasi }}
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-800 tracking-tight">
                        {{ $assetData->title }}
                    </h3>
                    <p class="text-xs text-slate-400 font-semibold mt-1">
                        {{ $assetData->code }}
                    </p>
                </div>

                @if($assetData->status_verifikasi === 'Perlu Verifikasi')
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <form action="{{ route('super-admin.verifikasi-aset.approve', [$type, $asset->id]) }}" method="POST" class="approval-form flex-1 sm:flex-none" data-action="memverifikasi" data-asset-name="{{ $assetData->title }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                                Verifikasi
                            </button>
                        </form>
                        <form action="{{ route('super-admin.verifikasi-aset.reject', [$type, $asset->id]) }}" method="POST" class="approval-form flex-1 sm:flex-none" data-action="menolak" data-asset-name="{{ $assetData->title }}">
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
                    <span class="text-sm font-bold text-slate-700">{{ $assetData->category }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kondisi</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetData->condition }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi / Ruangan</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetData->location ?: '-' }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Penanggung Jawab</span>
                    <span class="text-sm font-bold text-slate-700">{{ $assetData->responsible_person ?: '-' }}</span>
                </div>
                @if($assetData->owner !== '-')
                    <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Pemilik Aset</span>
                        <span class="text-sm font-bold text-slate-700">{{ $assetData->owner }}</span>
                    </div>
                @endif
                @if($assetData->value !== null)
                    <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nilai Perolehan</span>
                        <span class="text-sm font-bold text-slate-700">Rp {{ number_format($assetData->value, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">
                    Detail Teknis
                </h4>
                <div class="responsive-table rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($assetData->detail_rows as $label => $value)
                                <tr>
                                    <td class="py-3 px-4 bg-slate-50 text-slate-400 font-bold uppercase tracking-wider w-48">
                                        {{ $label }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 font-semibold">
                                        {{ $value }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">
                    Keterangan
                </h4>
                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed min-h-20">
                    {{ $assetData->description ?: 'Tidak ada keterangan tambahan.' }}
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
                        <span>Bidang</span>
                        <strong class="text-slate-800 text-right">{{ $asset->bidang->nama_bidang ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Diinput Oleh</span>
                        <strong class="text-slate-800 text-right">{{ $asset->inputter->nama ?? '-' }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                        <span>Tanggal Input</span>
                        <strong class="text-slate-800 text-right">{{ optional($asset->created_at)->format('d M Y H:i') }}</strong>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Diverifikasi Oleh</span>
                        <strong class="text-slate-800 text-right">{{ $asset->verifier->nama ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">
                    Dampak Verifikasi
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Aset yang sudah terverifikasi menjadi dasar untuk fitur QR Code dan pelaporan inventaris berikutnya.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.approval-form').forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const action = this.getAttribute('data-action');
                    const assetName = this.getAttribute('data-asset-name');

                    Swal.fire({
                        title: 'Konfirmasi Verifikasi',
                        text: `Apakah Anda yakin ingin ${action} aset "${assetName}"?`,
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
