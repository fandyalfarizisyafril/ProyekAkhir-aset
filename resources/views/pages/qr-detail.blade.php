<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Detail Aset - {{ $assetData->code }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8FAFC] text-slate-900 min-h-screen">
        <main class="max-w-3xl mx-auto px-4 py-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-[#002D84] text-white p-6">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <span class="px-3 py-1 text-[10px] font-extrabold tracking-wider rounded-md bg-white/15 border border-white/20">
                            {{ $assetData->type_label }}
                        </span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-100">
                            SIMA Diskominfotik Riau
                        </span>
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight leading-tight">
                        {{ $assetData->title }}
                    </h1>
                    <p class="text-sm font-semibold text-blue-100 mt-2">
                        {{ $assetData->code }}
                    </p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori</span>
                            <span class="text-sm font-bold text-slate-700">{{ $assetData->category }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kondisi</span>
                            <span class="text-sm font-bold text-slate-700">{{ $assetData->condition }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi</span>
                            <span class="text-sm font-bold text-slate-700">{{ $assetData->location ?: '-' }}</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Penanggung Jawab</span>
                            <span class="text-sm font-bold text-slate-700">{{ $assetData->responsible_person ?: '-' }}</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($assetData->detail_rows as $label => $value)
                                    <tr>
                                        <td class="py-3 px-4 bg-slate-50 text-slate-400 font-bold uppercase tracking-wider w-44">
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

                    <div>
                        <h2 class="text-xs font-bold text-slate-800 tracking-wider uppercase mb-3">
                            Keterangan
                        </h2>
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-sm text-slate-600 leading-relaxed">
                            {{ $assetData->description ?: 'Tidak ada keterangan tambahan.' }}
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-4 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                        Data ditampilkan dari QR Code aset terverifikasi.
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
