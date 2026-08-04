<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Permintaan Mutasi Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Isi kebutuhan aset agar Super Admin dapat memilih aset yang sesuai.
            </p>
        </div>

        <a href="{{ route('admin-perbidang.permintaan-mutasi.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="h-4 w-1 bg-[#0F3092] rounded inline-block"></span>
                    <span>Form Permintaan Mutasi</span>
                </h3>
            </div>

            <form action="{{ route('admin-perbidang.permintaan-mutasi.store') }}" method="POST" id="permintaan-mutasi-form" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="jenis_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Jenis Aset</label>
                        <select id="jenis_aset" name="jenis_aset" class="w-full bg-slate-50 border @error('jenis_aset') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium" required>
                            <option value="" disabled {{ old('jenis_aset') ? '' : 'selected' }}>Pilih jenis aset</option>
                            <option value="register" {{ old('jenis_aset') === 'register' ? 'selected' : '' }}>Register</option>
                            <option value="smki" {{ old('jenis_aset') === 'smki' ? 'selected' : '' }}>SMKI</option>
                        </select>
                        @error('jenis_aset') <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="kategori_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Kategori Aset</label>
                        <input id="kategori_aset" type="text" name="kategori_aset" value="{{ old('kategori_aset') }}" placeholder="Contoh: Peralatan TIK / elektronik" class="w-full bg-slate-50 border @error('kategori_aset') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium" required>
                        @error('kategori_aset') <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="nama_kebutuhan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Nama Kebutuhan / Nama Aset</label>
                        <input id="nama_kebutuhan" type="text" name="nama_kebutuhan" value="{{ old('nama_kebutuhan') }}" placeholder="Contoh: Laptop untuk operator layanan" class="w-full bg-slate-50 border @error('nama_kebutuhan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium" required>
                        @error('nama_kebutuhan') <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tanggal_permintaan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Tanggal Permintaan</label>
                        <input id="tanggal_permintaan" type="date" name="tanggal_permintaan" value="{{ old('tanggal_permintaan', now()->toDateString()) }}" class="w-full bg-slate-50 border @error('tanggal_permintaan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium" required>
                        @error('tanggal_permintaan') <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="lokasi_penggunaan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Lokasi Penggunaan</label>
                        <input id="lokasi_penggunaan" type="text" name="lokasi_penggunaan" value="{{ old('lokasi_penggunaan', auth()->user()->bidang->nama_ruangan ?? auth()->user()->bidang->nama_bidang ?? '') }}" placeholder="Contoh: Ruang layanan IKP" class="w-full bg-slate-50 border @error('lokasi_penggunaan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium" required>
                        @error('lokasi_penggunaan') <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="spesifikasi" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Spesifikasi / Keterangan Kebutuhan</label>
                        <textarea id="spesifikasi" name="spesifikasi" rows="4" placeholder="Tuliskan spesifikasi atau kriteria aset yang dibutuhkan..." class="w-full bg-slate-50 border @error('spesifikasi') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium">{{ old('spesifikasi') }}</textarea>
                        @error('spesifikasi') <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="alasan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">Alasan Permintaan</label>
                        <textarea id="alasan" name="alasan" rows="5" placeholder="Jelaskan alasan bidang membutuhkan aset tersebut..." class="w-full bg-slate-50 border @error('alasan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium" required>{{ old('alasan') }}</textarea>
                        @error('alasan') <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 border-t border-slate-100 pt-6">
                    <a href="{{ route('admin-perbidang.permintaan-mutasi.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors">Batal</a>
                    <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm">
                        Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">Alur Permintaan</span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Super Admin akan meninjau kebutuhan ini, memilih aset dari inventaris yang tersedia, lalu memutasikan aset ke bidang Anda jika disetujui.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
