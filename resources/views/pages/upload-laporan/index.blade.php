<x-app-layout>
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Upload Laporan
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Upload dokumen laporan aset yang sudah dicetak agar dapat dilihat oleh Kepala Dinas.
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

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 mb-6">
        <div class="mb-5">
            <h3 class="text-base font-bold text-slate-800 tracking-tight">Form Upload Laporan</h3>
            <p class="text-xs text-slate-400 mt-1">Pilih jenis laporan dan unggah file PDF, Excel, atau dokumen pendukung.</p>
        </div>
        <form action="{{ route('upload-laporan.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-7 gap-3 lg:items-end">
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

    @include('pages.upload-laporan._table', [
        'title' => 'Daftar Laporan Terupload',
        'subtitle' => 'Dokumen laporan yang sudah diupload dan tersedia untuk Kepala Dinas.',
    ])
</x-app-layout>
