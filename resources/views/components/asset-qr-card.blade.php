@props([
    'asset',
    'type',
])

@php
    $qrPath = $asset->qr_code_path;
    $qrUrl = $qrPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($qrPath) : null;
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <div class="flex items-center justify-between gap-3 mb-4">
        <h3 class="text-base font-bold text-slate-800 tracking-tight">QR Aset</h3>
        @if($qrPath)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-emerald-50 text-emerald-700 border border-emerald-200">
                Sudah QR
            </span>
        @endif
    </div>

    @if($asset->status_verifikasi !== 'Terverifikasi')
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs font-semibold text-amber-700">
            QR aktif setelah aset diverifikasi Super Admin.
        </div>
    @elseif($qrPath)
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="mx-auto aspect-square w-full max-w-[220px] rounded-xl border border-slate-200 bg-white p-3">
                <img src="{{ $qrUrl }}" alt="QR Code Aset" class="h-full w-full object-contain">
            </div>
            <p class="mt-3 text-center text-[11px] font-semibold text-slate-500">
                QR aktif dan siap dipindai.
            </p>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3">
            <a href="{{ route('qr.asset.show', [$type, $asset->id]) }}" target="_blank" class="inline-flex w-full items-center justify-center bg-[#0F3092] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-4 py-3 rounded-xl transition-colors">
                Lihat Detail QR
            </a>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('qr.asset.label', [$type, $asset->id]) }}" target="_blank" class="inline-flex w-full items-center justify-center border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold uppercase tracking-wider px-4 py-3 rounded-xl transition-colors">
                    Print QR
                </a>
                <a href="{{ route('qr.asset.download', [$type, $asset->id]) }}" class="inline-flex w-full items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider px-4 py-3 rounded-xl transition-colors">
                    Download QR
                </a>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs font-semibold text-slate-500">
            QR belum diaktifkan oleh Super Admin.
        </div>
    @endif
</div>
