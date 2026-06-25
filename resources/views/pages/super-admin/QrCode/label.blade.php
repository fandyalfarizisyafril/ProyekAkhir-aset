<x-app-layout>
    @php
        $qrDisk = \Illuminate\Support\Facades\Storage::disk('public');
        $qrSvg = $qrDisk->exists($qrPath) && str_ends_with(strtolower($qrPath), '.svg')
            ? $qrDisk->get($qrPath)
            : null;
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Label QR Code Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Cetak atau unduh label untuk ditempelkan pada aset fisik.
            </p>
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('super-admin.qr-code.index') }}" class="flex-1 sm:flex-none border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
                Kembali
            </a>
            <a href="{{ route('super-admin.qr-code.download', [$type, $asset->id]) }}" class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
                Download
            </a>
            <button type="button" onclick="window.print()" class="flex-1 sm:flex-none bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
                Print
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 max-w-3xl mx-auto print:shadow-none print:border-0 print:p-0">
        <div class="border-2 border-slate-800 rounded-xl p-6 print:border-black">
            <div class="flex flex-col sm:flex-row gap-6 items-center">
                <div class="w-52 h-52 flex items-center justify-center border border-slate-200 rounded-lg p-3 bg-white flex-shrink-0">
                    @if($qrSvg)
                        <div class="w-full h-full [&>svg]:block [&>svg]:h-full [&>svg]:w-full">
                            {!! $qrSvg !!}
                        </div>
                    @else
                        <img src="{{ $qrUrl }}" alt="QR Code {{ $assetData->code }}" class="w-full h-full object-contain">
                    @endif
                </div>

                <div class="flex-1 text-center sm:text-left space-y-4">
                    <div>
                        <span class="inline-block px-3 py-1 text-[10px] font-extrabold tracking-wider rounded-md bg-slate-100 text-slate-700 border border-slate-300">
                            {{ $assetData->type_label }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ $assetData->title }}
                        </h3>
                        <p class="text-sm font-bold text-slate-500 mt-1">
                            {{ $assetData->code }}
                        </p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider">Bidang</span>
                            <span class="block text-slate-800 font-bold mt-1">{{ $asset->bidang->nama_bidang ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider">Kondisi</span>
                            <span class="block text-slate-800 font-bold mt-1">{{ $assetData->condition }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider">Lokasi</span>
                            <span class="block text-slate-800 font-bold mt-1">{{ $assetData->location ?: '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-slate-400 font-bold uppercase tracking-wider">PJ</span>
                            <span class="block text-slate-800 font-bold mt-1">{{ $assetData->responsible_person ?: '-' }}</span>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-200">
                        <p class="text-[10px] font-semibold text-slate-500 break-all">
                            {{ $scanUrl }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
