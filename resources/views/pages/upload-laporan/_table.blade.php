@php
    $formatFileSize = function ($bytes) {
        if (!$bytes) {
            return '-';
        }

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', '.') . ' MB'
            : number_format($bytes / 1024, 1, ',', '.') . ' KB';
    };
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
    <div class="flex flex-col sm:flex-row justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-slate-800 tracking-tight">
                {{ $title ?? 'Daftar Laporan Terupload' }}
            </h3>
            <p class="text-xs text-slate-400 mt-1">
                {{ $subtitle ?? 'Dokumen laporan yang tersedia untuk ditinjau Kepala Dinas.' }}
            </p>
        </div>
        <span class="inline-flex items-center self-start rounded-full bg-slate-50 border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600">
            {{ $uploadedReports->total() }} Dokumen
        </span>
    </div>

    <div class="responsive-table">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <th class="py-4 px-4">Laporan</th>
                    <th class="py-4 px-4">Jenis Aset</th>
                    <th class="py-4 px-4">Diupload Oleh</th>
                    <th class="py-4 px-4">Bidang</th>
                    <th class="py-4 px-4">Ukuran</th>
                    <th class="py-4 px-4">Tanggal Upload</th>
                    <th class="py-4 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                @forelse($uploadedReports as $report)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 px-4">
                            <div class="font-bold text-slate-800 text-sm">{{ $report->jenis_laporan }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">
                                {{ $report->file_original_name ?? basename($report->file_path) }}
                            </div>
                            @if($report->keterangan)
                                <div class="text-[10px] text-slate-500 mt-2 max-w-md">{{ $report->keterangan }}</div>
                            @endif
                        </td>
                        <td class="py-4 px-4 font-bold text-slate-700 uppercase tracking-wide">{{ $report->jenis_aset }}</td>
                        <td class="py-4 px-4">
                            <div class="font-semibold text-slate-700">{{ $report->creator->nama ?? $report->creator->name ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 mt-1">{{ $report->creator->role ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-4 font-semibold text-slate-600">{{ $report->creator->bidang->nama_bidang ?? '-' }}</td>
                        <td class="py-4 px-4 font-semibold text-slate-600">{{ $formatFileSize($report->file_size) }}</td>
                        <td class="py-4 px-4 font-semibold text-slate-600">{{ $report->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        <td class="py-4 px-4">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('laporan-aset.view', $report) }}" target="_blank" class="inline-flex items-center justify-center text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Lihat Laporan">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <a href="{{ route('laporan-aset.download', $report) }}" class="inline-flex items-center justify-center text-emerald-600 hover:text-emerald-700 transition-colors p-1 hover:bg-emerald-50 rounded" title="Download Laporan">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                            Belum ada dokumen laporan yang diupload.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($uploadedReports->hasPages())
        <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
            <div>
                Menampilkan {{ $uploadedReports->firstItem() ?? 0 }}-{{ $uploadedReports->lastItem() ?? 0 }} dari {{ $uploadedReports->total() }} dokumen laporan
            </div>
            <div>
                {{ $uploadedReports->links() }}
            </div>
        </div>
    @endif
</div>
