<x-app-layout>
    @php
        $formatNumber = fn ($value) => number_format((int) $value, 0, ',', '.');
        $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $formatFileSize = function ($bytes) {
            if (!$bytes) {
                return '-';
            }

            return $bytes >= 1048576
                ? number_format($bytes / 1048576, 1, ',', '.') . ' MB'
                : number_format($bytes / 1024, 1, ',', '.') . ' KB';
        };
        $hasFilter = $filters['start_date'] || $filters['end_date'] || $filters['jenis'] !== 'Semua Jenis' || (!$isAdminPerbidang && $filters['bidang_id'] !== 'Semua Bidang') || $filters['kategori'] !== 'Semua Kategori' || $filters['kondisi'] !== 'Semua Kondisi';
    @endphp

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Laporan Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                {{ $isKepalaDinas ? 'Daftar rekap laporan aset yang diupload oleh Super Admin dan Admin Perbidang.' : 'Rekapitulasi aset periodik dan upload dokumen laporan untuk Kepala Dinas.' }}
            </p>
        </div>
        @if(!$isKepalaDinas)
            <div class="flex flex-wrap gap-3 w-full lg:w-auto">
                <a href="{{ route('laporan-aset.print', request()->query()) }}" target="_blank" class="flex-1 lg:flex-none bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center gap-2 transition-all duration-150 shadow-sm">
                    <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H8v-4z" />
                    </svg>
                    <span>Cetak / PDF</span>
                </a>
                <a href="{{ route('laporan-aset.export', request()->query()) }}" class="flex-1 lg:flex-none bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center gap-2 transition-all duration-150 shadow-sm">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Export Excel</span>
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-emerald-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(!$isKepalaDinas)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 mb-6">
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-800 tracking-tight">Upload Laporan</h3>
                <p class="text-xs text-slate-400 mt-1">Upload file laporan yang sudah disimpan agar dapat dilihat oleh Kepala Dinas.</p>
            </div>
            <form action="{{ route('laporan-aset.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-7 gap-3 lg:items-end">
                @csrf
                <div>
                    <label for="jenis_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Jenis Aset</label>
                    <select id="jenis_aset" name="jenis_aset" class="w-full bg-white border @error('jenis_aset') border-rose-300 @else border-slate-200 @enderror text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        @foreach($uploadJenisAsetOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('jenis_aset') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jenis_laporan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Jenis Laporan</label>
                    <select id="jenis_laporan" name="jenis_laporan" class="w-full bg-white border @error('jenis_laporan') border-rose-300 @else border-slate-200 @enderror text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                        @foreach($uploadJenisLaporanOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('jenis_laporan') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="keterangan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Keterangan</label>
                    <input id="keterangan" type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="Contoh: Rekap aset bulan Juni 2026" class="w-full bg-white border @error('keterangan') border-rose-300 @else border-slate-200 @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                </div>
                <div class="lg:col-span-2">
                    <label for="file" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">File Laporan</label>
                    <input id="file" type="file" name="file" accept=".pdf,.xls,.xlsx,.doc,.docx" class="w-full bg-white border @error('file') border-rose-300 @else border-slate-200 @enderror text-slate-500 text-xs rounded-xl px-4 py-2.5 focus:outline-none transition-colors font-medium file:mr-4 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                </div>
                <div>
                    <button type="submit" class="w-full bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                        Upload
                    </button>
                </div>
                @if($errors->any())
                    <div class="lg:col-span-7 text-xs text-rose-600 font-semibold">
                        {{ $errors->first() }}
                    </div>
                @endif
            </form>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">
                    {{ $isKepalaDinas ? 'Daftar Rekap Laporan' : 'Daftar Laporan Terupload' }}
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $isKepalaDinas ? 'Dokumen laporan yang diupload oleh Super Admin dan Admin Perbidang.' : 'Dokumen laporan yang tersedia untuk ditinjau Kepala Dinas.' }}
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

    @if(!$isKepalaDinas)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 md:col-span-1">
                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Nilai Aset</p>
                <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ $formatCurrency($summary['registerValue']) }}</div>
                <p class="text-xs text-slate-400 mt-1">{{ $formatNumber($summary['deleted']) }} aset nonaktif pada periode</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 mb-6">
            <form action="{{ route('laporan-aset.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-7 gap-3 md:items-end">
                <div>
                    <label for="start_date" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Dari Tanggal</label>
                    <input id="start_date" type="date" name="start_date" value="{{ $filters['start_date'] }}" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                </div>
                <div>
                    <label for="end_date" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Sampai Tanggal</label>
                    <input id="end_date" type="date" name="end_date" value="{{ $filters['end_date'] }}" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                </div>
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
                    <select id="bidang_id" name="bidang_id" {{ $isAdminPerbidang ? 'disabled' : '' }} class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium disabled:bg-slate-50 disabled:text-slate-400">
                        @if(!$isAdminPerbidang)
                            <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                        @endif
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
                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm">
                        Terapkan
                    </button>
                </div>
            </form>

            @if($hasFilter)
                <a href="{{ route('laporan-aset.index') }}" class="inline-block mt-4 text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold hover:underline">
                    Reset Filter
                </a>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">
                    Daftar Rekap Aset
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Menampilkan aset aktif terverifikasi sesuai filter laporan.
                </p>
            </div>
            <span class="inline-flex items-center self-start rounded-full bg-slate-50 border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600">
                {{ $assets->total() }} Data
            </span>
        </div>

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Aset</th>
                        <th class="py-4 px-4">Jenis</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Kondisi</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4">Nilai</th>
                        <th class="py-4 px-4">Tanggal Input</th>
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
                                    <span>{{ $asset->category ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-700 uppercase tracking-wide">{{ $asset->type_label }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600">{{ $asset->bidang->nama_bidang ?? '-' }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600">{{ $asset->condition ?? '-' }}</td>
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-700">{{ $asset->status }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">{{ $asset->verification_status }}</div>
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">{{ $asset->value === null ? '-' : $formatCurrency($asset->value) }}</td>
                            <td class="py-4 px-4 font-semibold text-slate-600">{{ $asset->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Belum ada aset terverifikasi yang cocok dengan filter laporan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} data laporan
                </div>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
        </div>
    @endif
</x-app-layout>
