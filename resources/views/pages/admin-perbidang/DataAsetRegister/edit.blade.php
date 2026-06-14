<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            Ubah Informasi Aset Register / Fisik
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Silakan ubah detail informasi aset register yang terdaftar di bawah ini.
        </p>
    </div>

    <!-- Main Stack Form -->
    <form action="{{ route('admin-perbidang.data-aset-register.update', $asset->id) }}" method="POST" id="edit-asset-form" class="space-y-8 max-w-7xl">
        @csrf
        @method('PUT')

        <!-- Card 1: Informasi Identitas Aset -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <!-- Info Icon -->
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-bold text-slate-800">Informasi Identitas Aset</span>
                </div>
                <span class="bg-[#EFF6FF] border border-[#BFDBFE] text-[#1E40AF] text-[10px] font-bold px-2.5 py-1 rounded-md uppercase">
                    {{ $asset->kode_aset }}
                </span>
            </div>

            <!-- Row 1: Kode Aset & Nama Aset -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kode Aset -->
                <div>
                    <label for="kode_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        KODE ASET
                    </label>
                    <input 
                        type="text" 
                        id="kode_aset" 
                        name="kode_aset" 
                        value="{{ old('kode_aset', $asset->kode_aset) }}"
                        placeholder="Contoh: AST-2023-001"
                        class="w-full bg-slate-50 border @error('kode_aset') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('kode_aset')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Aset -->
                <div>
                    <label for="nama_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        NAMA ASET
                    </label>
                    <input 
                        type="text" 
                        id="nama_aset" 
                        name="nama_aset" 
                        value="{{ old('nama_aset', $asset->nama_aset) }}"
                        placeholder="Masukkan nama lengkap aset"
                        class="w-full bg-slate-50 border @error('nama_aset') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('nama_aset')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 2: Kode Barang, Kode Urut Barang, Status Barang -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Kategori / Kode Barang -->
                <div>
                    <label for="kode_barang" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        KATEGORI / KODE BARANG
                    </label>
                    <input
                        type="text"
                        id="kode_barang"
                        name="kode_barang"
                        value="{{ old('kode_barang', $asset->kode_barang) }}"
                        list="kategori-register-options"
                        placeholder="Ketik atau pilih kategori Register"
                        class="w-full bg-slate-50 border @error('kode_barang') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    <datalist id="kategori-register-options">
                        @foreach($categories as $category)
                            <option value="{{ $category }}"></option>
                        @endforeach
                    </datalist>
                    @error('kode_barang')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kode Urut Barang -->
                <div>
                    <label for="kode_urut_barang" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        KODE URUT BARANG
                    </label>
                    <input 
                        type="text" 
                        id="kode_urut_barang" 
                        name="kode_urut_barang" 
                        value="{{ old('kode_urut_barang', $asset->kode_urut_barang) }}"
                        placeholder="0001"
                        class="w-full bg-slate-50 border @error('kode_urut_barang') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('kode_urut_barang')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Barang -->
                <div>
                    <label for="status_barang" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        STATUS BARANG
                    </label>
                    <div class="relative">
                        <select 
                            id="status_barang" 
                            name="status_barang" 
                            class="w-full bg-slate-50 border @error('status_barang') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                        >
                            <option value="Baik" {{ old('status_barang', $asset->status_barang) === 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Rusak Ringan" {{ old('status_barang', $asset->status_barang) === 'Rusak Ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                            <option value="Rusak Berat" {{ old('status_barang', $asset->status_barang) === 'Rusak Berat' ? 'selected' : '' }}>Rusak Berat</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('status_barang')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Card 2: Penempatan & Kepemilikan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                <!-- Location Pin Icon -->
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm font-bold text-slate-800">Penempatan & Kepemilikan</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kode Bidang / Bagian -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        KODE BIDANG / BAGIAN
                    </label>
                    <input 
                        type="text" 
                        disabled 
                        value="{{ auth()->user()->bidang->nama_bidang ?? 'Bidang Persandian' }}" 
                        placeholder="Pilih unit kerja"
                        class="w-full bg-slate-50 border border-slate-200 text-slate-500 text-xs rounded-xl px-4 py-3.5 focus:outline-none font-medium cursor-not-allowed"
                    >
                    <input type="hidden" name="bidang_id" value="{{ auth()->user()->bidang_id }}">
                </div>

                <!-- Pemilik Aset -->
                <div>
                    <label for="pemilik_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        PEMILIK ASET
                    </label>
                    <input 
                        type="text" 
                        id="pemilik_aset" 
                        name="pemilik_aset" 
                        value="{{ old('pemilik_aset', $asset->pemilik_aset) }}"
                        placeholder="Instansi pemilik"
                        class="w-full bg-slate-50 border @error('pemilik_aset') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('pemilik_aset')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pengguna (Personel) -->
                <div>
                    <label for="pengguna" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        PENGGUNA (PERSONEL)
                    </label>
                    <input 
                        type="text" 
                        id="pengguna" 
                        name="pengguna" 
                        value="{{ old('pengguna', $asset->pengguna) }}"
                        placeholder="Nama pemakai aset"
                        class="w-full bg-slate-50 border @error('pengguna') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('pengguna')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lokasi Aset -->
                <div>
                    <label for="lokasi_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        LOKASI ASET
                    </label>
                    <input 
                        type="text" 
                        id="lokasi_aset" 
                        name="lokasi_aset" 
                        value="{{ old('lokasi_aset', $asset->lokasi_aset) }}"
                        placeholder="Ruang / Gedung / Koordinat"
                        class="w-full bg-slate-50 border @error('lokasi_aset') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('lokasi_aset')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Row 3: Grid of Left and Right Card -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Card 3: Klasifikasi & Keamanan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                    <!-- Security/Shield Icon -->
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="text-sm font-bold text-slate-800">Klasifikasi & Keamanan</span>
                </div>

                <!-- Metode Pemusnahan -->
                <div>
                    <label for="metode_pemusnahan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        METODE PEMUSNAHAN
                    </label>
                    <div class="relative">
                        <select 
                            id="metode_pemusnahan" 
                            name="metode_pemusnahan" 
                            class="w-full bg-slate-50 border @error('metode_pemusnahan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                        >
                            <option value="Dihapus / Bakar" {{ old('metode_pemusnahan', $asset->metode_pemusnahan) === 'Dihapus / Bakar' ? 'selected' : '' }}>Dihapus / Bakar</option>
                            <option value="Penjualan" {{ old('metode_pemusnahan', $asset->metode_pemusnahan) === 'Penjualan' ? 'selected' : '' }}>Penjualan</option>
                            <option value="Hibah" {{ old('metode_pemusnahan', $asset->metode_pemusnahan) === 'Hibah' ? 'selected' : '' }}>Hibah</option>
                            <option value="Lainnya" {{ old('metode_pemusnahan', $asset->metode_pemusnahan) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('metode_pemusnahan')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kerahasiaan -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-3">
                        KERAHASIAAN
                    </label>
                    <div class="flex flex-wrap items-center gap-x-8 gap-y-2 mt-2">
                        <label class="flex items-center cursor-pointer text-xs font-bold text-slate-700 select-none">
                            <input type="radio" name="kerahasiaan" value="Umum" {{ old('kerahasiaan', $asset->kerahasiaan) === 'Umum' ? 'checked' : '' }} class="h-4 w-4 text-[#002D84] border-slate-300 focus:ring-[#002D84] mr-2">
                            <span>Umum</span>
                        </label>
                        <label class="flex items-center cursor-pointer text-xs font-bold text-slate-700 select-none">
                            <input type="radio" name="kerahasiaan" value="Terbatas" {{ old('kerahasiaan', $asset->kerahasiaan) === 'Terbatas' ? 'checked' : '' }} class="h-4 w-4 text-[#002D84] border-slate-300 focus:ring-[#002D84] mr-2">
                            <span>Terbatas</span>
                        </label>
                        <label class="flex items-center cursor-pointer text-xs font-bold text-slate-700 select-none">
                            <input type="radio" name="kerahasiaan" value="Rahasia" {{ old('kerahasiaan', $asset->kerahasiaan) === 'Rahasia' ? 'checked' : '' }} class="h-4 w-4 text-[#002D84] border-slate-300 focus:ring-[#002D84] mr-2">
                            <span>Rahasia</span>
                        </label>
                    </div>
                    @error('kerahasiaan')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kritikalitas -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-3">
                        KRITIKALITAS
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Rendah -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="kritikalitas" value="RENDAH" {{ old('kritikalitas', $asset->kritikalitas) === 'RENDAH' ? 'checked' : '' }} class="sr-only peer">
                            <div class="flex items-center justify-center py-2.5 rounded-xl text-xs font-bold tracking-wider transition-all bg-slate-50 border border-slate-200 text-slate-400 hover:bg-slate-100 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 peer-checked:ring-1 peer-checked:ring-emerald-500 peer-checked:shadow-sm">
                                RENDAH
                            </div>
                        </label>
                        <!-- Sedang -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="kritikalitas" value="SEDANG" {{ old('kritikalitas', $asset->kritikalitas) === 'SEDANG' ? 'checked' : '' }} class="sr-only peer">
                            <div class="flex items-center justify-center py-2.5 rounded-xl text-xs font-bold tracking-wider transition-all bg-slate-50 border border-slate-200 text-slate-400 hover:bg-slate-100 peer-checked:bg-amber-50 peer-checked:border-amber-500 peer-checked:text-amber-700 peer-checked:ring-1 peer-checked:ring-amber-500 peer-checked:shadow-sm">
                                SEDANG
                            </div>
                        </label>
                        <!-- Tinggi -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="kritikalitas" value="TINGGI" {{ old('kritikalitas', $asset->kritikalitas) === 'TINGGI' ? 'checked' : '' }} class="sr-only peer">
                            <div class="flex items-center justify-center py-2.5 rounded-xl text-xs font-bold tracking-wider transition-all bg-slate-50 border border-slate-200 text-slate-400 hover:bg-slate-100 peer-checked:bg-rose-50 peer-checked:border-rose-500 peer-checked:text-rose-700 peer-checked:ring-1 peer-checked:ring-rose-500 peer-checked:shadow-sm">
                                TINGGI
                            </div>
                        </label>
                    </div>
                    @error('kritikalitas')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Card 4: Nilai & Keterangan -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                    <!-- Banknote/Card Icon -->
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="text-sm font-bold text-slate-800">Nilai & Keterangan</span>
                </div>

                <!-- Nilai Perolehan -->
                <div>
                    <label for="nilai" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        NILAI PEROLEHAN (RP)
                    </label>
                    <div class="relative flex items-stretch shadow-sm rounded-xl">
                        <span class="flex items-center px-4 bg-slate-50 border border-slate-200 text-slate-500 text-xs font-bold rounded-l-xl border-r-0">
                            Rp
                        </span>
                        <input 
                            type="number" 
                            step="0.01"
                            id="nilai" 
                            name="nilai" 
                            value="{{ old('nilai', $asset->nilai) }}"
                            placeholder="0"
                            class="w-full bg-slate-50 border @error('nilai') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-r-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                    </div>
                    @error('nilai')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan Tambahan -->
                <div>
                    <label for="keterangan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        KETERANGAN TAMBAHAN
                    </label>
                    <textarea 
                        id="keterangan" 
                        name="keterangan" 
                        rows="4"
                        placeholder="Catatan spesifikasi atau kondisi teknis..."
                        class="w-full bg-slate-50 border @error('keterangan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium"
                    >{{ old('keterangan', $asset->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>

        <!-- Action Footer -->
        <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-100">
            <!-- Reset Form Button -->
            <button type="reset" class="text-slate-400 hover:text-slate-600 text-xs font-bold uppercase tracking-wider transition-colors">
                Reset Form
            </button>
            
            <div class="flex items-center space-x-4">
                <!-- Batal Button -->
                <a href="{{ route('admin-perbidang.data-aset-register.index') }}" class="px-6 py-2.5 border border-slate-200 hover:bg-slate-50 text-slate-500 hover:text-slate-700 text-xs font-bold uppercase tracking-wider rounded-xl transition-all duration-150 shadow-sm flex items-center justify-center">
                    Batal
                </a>
                <!-- Simpan Perubahan Button -->
                <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3 rounded-xl transition-all duration-150 shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <!-- SweetAlert2 Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('edit-asset-form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (this.getAttribute('data-confirmed') === 'true') {
                        return;
                    }
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Ubah',
                        text: 'Apakah Anda yakin ingin memperbarui data aset register ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#002D84',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, Perbarui!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.setAttribute('data-confirmed', 'true');
                            this.submit();
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>
