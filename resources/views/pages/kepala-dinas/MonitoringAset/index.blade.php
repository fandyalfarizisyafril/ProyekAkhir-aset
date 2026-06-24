<x-app-layout>
    @php
        $formatCurrency = fn ($value) => $value === null ? '-' : 'Rp ' . number_format((float) $value, 0, ',', '.');
        $hasFilter = $filters['jenis'] !== 'Semua Jenis' || $filters['bidang_id'] !== 'Semua Bidang' || $filters['kategori'] !== 'Semua Kategori' || $filters['kondisi'] !== 'Semua Kondisi' || $filters['status'] !== 'Semua Status' || $filters['search'];
    @endphp

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            {{ $pageTitle }}
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            {{ $pageSubtitle }}
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach($summaryCards as $card)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">{{ $card['label'] }}</p>
                <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ $card['value'] }}</div>
                <p class="text-xs text-slate-400 mt-1">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 mb-6">
        @if($mode === 'data')
            <form action="{{ route($routeName) }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <label for="jenis" class="sr-only">Tipe</label>
                    <select id="jenis" name="jenis" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Tipe</option>
                        <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>Register</option>
                        <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
                    </select>
                </div>

                <div>
                    <label for="bidang_id" class="sr-only">Bidang</label>
                    <select id="bidang_id" name="bidang_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                        @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>{{ $bidang->nama_bidang }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama aset, kode, kategori, atau bidang..." class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
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
            </form>
        @elseif($mode === 'kondisi')
            <form action="{{ route($routeName) }}" method="GET" class="space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-56">
                            <label for="jenis" class="sr-only">Kategori</label>
                            <select id="jenis" name="jenis" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl pl-4 pr-10 py-2.5 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                                <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Kategori</option>
                                <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>REGISTER</option>
                                <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <div class="relative w-full sm:w-56">
                            <label for="kondisi" class="sr-only">Kondisi</label>
                            <select id="kondisi" name="kondisi" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl pl-4 pr-10 py-2.5 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                                <option value="Semua Kondisi" {{ $filters['kondisi'] === 'Semua Kondisi' ? 'selected' : '' }}>Semua Kondisi</option>
                                @foreach($conditionOptions as $condition)
                                    <option value="{{ $condition }}" {{ $filters['kondisi'] === $condition ? 'selected' : '' }}>{{ $condition }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="relative w-full md:w-80 shadow-sm rounded-xl">
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama atau kode aset..." class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-2.5 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
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
            </form>
        @else
            <form action="{{ route($routeName) }}" method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div>
                    <label for="jenis" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Jenis</label>
                    <select id="jenis" name="jenis" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>Register</option>
                        <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
                    </select>
                </div>

                <div>
                    <label for="bidang_id" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Bidang</label>
                    <select id="bidang_id" name="bidang_id" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                        @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>{{ $bidang->nama_bidang }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="kategori" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Kategori</label>
                    <select id="kategori" name="kategori" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Kategori" {{ $filters['kategori'] === 'Semua Kategori' ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach($categoryOptions as $category)
                            <option value="{{ $category }}" {{ $filters['kategori'] === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="kondisi" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Kondisi</label>
                    <select id="kondisi" name="kondisi" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Kondisi" {{ $filters['kondisi'] === 'Semua Kondisi' ? 'selected' : '' }}>Semua Kondisi</option>
                        @foreach($conditionOptions as $condition)
                            <option value="{{ $condition }}" {{ $filters['kondisi'] === $condition ? 'selected' : '' }}>{{ $condition }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Status</label>
                    <select id="status" name="status" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        <option value="Semua Status" {{ $filters['status'] === 'Semua Status' ? 'selected' : '' }}>Semua Status</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" {{ $filters['status'] === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                        Terapkan
                    </button>
                </div>

                <div class="md:col-span-6">
                    <div class="relative w-full md:w-[520px]">
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama aset, kode, kategori, lokasi..." class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
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
            </form>
        @endif

        @if($hasFilter)
            <a href="{{ route($routeName) }}" class="inline-block mt-4 text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold hover:underline">
                Reset Filter
            </a>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">
                    Daftar Aset Terpantau
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Seluruh data diambil dari aset terverifikasi dan masih aktif.
                </p>
            </div>
            <span class="inline-flex items-center self-start rounded-full bg-slate-50 border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600">
                {{ $assets->total() }} Data
            </span>
        </div>

        <div class="responsive-table">
            @if($mode === 'data')
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                            <th class="py-4 px-4">Nama Aset</th>
                            <th class="py-4 px-4">Tipe</th>
                            <th class="py-4 px-4">Bidang</th>
                            <th class="py-4 px-4">Status</th>
                            <th class="py-4 px-4">Nilai</th>
                            <th class="py-4 px-4">Dibuat</th>
                            <th class="py-4 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-800 text-sm">{{ $asset->name }}</div>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        <span class="font-semibold text-slate-600">{{ $asset->code }}</span>
                                        <span class="px-1">|</span>
                                        <span>{{ $asset->category }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 {{ $asset->type === 'register' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                        {{ $asset->type_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-600">{{ $asset->bidang->nama_bidang ?? '-' }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-700">{{ $asset->status }}</td>
                                <td class="py-4 px-4 font-bold text-slate-800">{{ $formatCurrency($asset->value) }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-600">{{ $asset->created_at?->format('d M Y') ?? '-' }}</td>
                                <td class="py-4 px-4 text-center">
                                    <a href="{{ $asset->detail_route }}?from={{ $mode }}" class="inline-flex items-center justify-center text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Detail Aset">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                    Belum ada aset terverifikasi yang cocok dengan filter monitoring.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($mode === 'kondisi')
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                            <th class="py-4 px-4">Nama Aset & Kode</th>
                            <th class="py-4 px-4">Kategori</th>
                            <th class="py-4 px-4">Lokasi</th>
                            <th class="py-4 px-4">Kondisi</th>
                            <th class="py-4 px-4">Update Terakhir</th>
                            <th class="py-4 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($assets as $asset)
                            @php
                                $assetName = strtolower($asset->name);

                                if (str_contains($assetName, 'macbook') || str_contains($assetName, 'laptop') || str_contains($assetName, 'notebook') || str_contains($assetName, 'pc') || str_contains($assetName, 'computer') || str_contains($assetName, 'server') || str_contains($assetName, 'dell')) {
                                    $iconBg = 'bg-[#EFF6FF] text-[#2563EB]';
                                    $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';
                                } elseif (str_contains($assetName, 'printer') || str_contains($assetName, 'epson') || str_contains($assetName, 'canon')) {
                                    $iconBg = 'bg-[#F1F5F9] text-[#475569]';
                                    $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>';
                                } elseif (str_contains($assetName, 'router') || str_contains($assetName, 'cisco') || str_contains($assetName, 'switch') || str_contains($assetName, 'network') || str_contains($assetName, 'hub') || str_contains($assetName, 'ap') || str_contains($assetName, 'access point')) {
                                    $iconBg = 'bg-[#EEF2F6] text-[#4F46E5]';
                                    $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a10.5 10.5 0 0114.14 0M1.414 6.586a16.5 16.5 0 0121.172 0" /></svg>';
                                } else {
                                    $iconBg = 'bg-[#F0FDFA] text-[#0D9488]';
                                    $iconSvg = '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>';
                                }

                                $dotColor = match ($asset->condition) {
                                    'Baik' => 'bg-emerald-500',
                                    'Rusak Ringan' => 'bg-amber-500',
                                    default => 'bg-rose-500',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center space-x-3.5">
                                        <div class="h-10 w-10 {{ $iconBg }} rounded-xl flex items-center justify-center flex-shrink-0">
                                            {!! $iconSvg !!}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm">{{ $asset->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-semibold tracking-wide mt-0.5 uppercase">{{ $asset->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded border tracking-wide inline-block uppercase {{ $asset->type === 'register' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                        {{ strtoupper($asset->type_label) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-500">{{ $asset->location ?? '-' }}</td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center font-bold text-xs text-slate-700">
                                        <span class="mr-2 h-2.5 w-2.5 rounded-full {{ $dotColor }} border border-white shadow-sm"></span>
                                        {{ $asset->condition ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-medium text-slate-500">{{ $asset->last_update?->translatedFormat('d M Y') ?? '-' }}</td>
                                <td class="py-4 px-4 text-right">
                                    <a href="{{ $asset->detail_route }}?from={{ $mode }}" class="inline-flex items-center justify-end text-[#0F3092] hover:text-[#0B2F83] text-[11px] font-extrabold uppercase tracking-wider transition-colors" title="Lihat Detail">
                                        <span>Lihat</span>
                                        <svg class="h-4 w-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                    Tidak ada data kondisi aset yang cocok dengan filter monitoring.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                            <th class="py-4 px-4">Aset</th>
                            <th class="py-4 px-4">Jenis</th>
                            <th class="py-4 px-4">Bidang</th>
                            <th class="py-4 px-4">Kondisi</th>
                            <th class="py-4 px-4">Status</th>
                            <th class="py-4 px-4">Nilai</th>
                            <th class="py-4 px-4 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-800 text-sm">{{ $asset->name }}</div>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        <span class="font-semibold text-slate-600">{{ $asset->code }}</span>
                                        <span class="px-1">|</span>
                                        <span>{{ $asset->category }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1">Lokasi: {{ $asset->location ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-700 uppercase tracking-wide">{{ $asset->type_label }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-600">{{ $asset->bidang->nama_bidang ?? '-' }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-600">{{ $asset->condition ?? '-' }}</td>
                                <td class="py-4 px-4 font-semibold text-slate-700">{{ $asset->status }}</td>
                                <td class="py-4 px-4 font-bold text-slate-800">{{ $formatCurrency($asset->value) }}</td>
                                <td class="py-4 px-4 text-center">
                                    <a href="{{ $asset->detail_route }}?from={{ $mode }}" class="inline-flex items-center justify-center text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Detail Aset">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                    Belum ada aset terverifikasi yang cocok dengan filter monitoring.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if($assets->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} data monitoring
                </div>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
