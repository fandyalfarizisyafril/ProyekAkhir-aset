<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            Pendaftaran Aset Baru SMKI
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Silakan lengkapi detail informasi aset sesuai dengan kategori yang dipilih.
        </p>
    </div>

    <!-- Main Grid: Form and Side Guides -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Form Container (Spans 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="h-4 w-1 bg-[#0F3092] rounded inline-block"></span>
                    <span>Form Detail Aset SMKI</span>
                </h3>
                <span class="bg-slate-50 border border-slate-200 text-slate-400 text-[10px] font-bold px-2.5 py-1 rounded-md">
                    BARU
                </span>
            </div>

            <form action="{{ route('admin-perbidang.data-aset-smki.store') }}" method="POST" id="create-asset-form" class="space-y-6">
                @csrf

                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kategori / Jenis Barang -->
                    <div class="md:col-span-2">
                        <label for="jenis_barang" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KATEGORI / JENIS BARANG
                        </label>
                        <select
                            id="jenis_barang"
                            name="jenis_barang"
                            class="w-full bg-slate-50 border @error('jenis_barang') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            required
                        >
                            <option value="" disabled {{ old('jenis_barang') ? '' : 'selected' }}>Pilih kategori SMKI</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ old('jenis_barang') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('jenis_barang')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Merk / Model -->
                    <div>
                        <label for="merk_model" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            MERK / MODEL
                        </label>
                        <input 
                            type="text" 
                            id="merk_model" 
                            name="merk_model" 
                            value="{{ old('merk_model') }}"
                            placeholder="Masukkan merk"
                            class="w-full bg-slate-50 border @error('merk_model') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                        @error('merk_model')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Seri Pabrik -->
                    <div>
                        <label for="no_ser_model" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            NOMOR SERI PABRIK
                        </label>
                        <input 
                            type="text" 
                            id="no_ser_model" 
                            name="no_ser_model" 
                            value="{{ old('no_ser_model') }}"
                            placeholder="SN-XXXXX-XXXX"
                            class="w-full bg-slate-50 border @error('no_ser_model') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                        @error('no_ser_model')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ukuran -->
                    <div>
                        <label for="ukuran" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            UKURAN
                        </label>
                        <input 
                            type="text" 
                            id="ukuran" 
                            name="ukuran" 
                            value="{{ old('ukuran') }}"
                            placeholder="Dimensi atau spesifikasi"
                            class="w-full bg-slate-50 border @error('ukuran') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                        @error('ukuran')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bahan -->
                    <div>
                        <label for="bahan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            BAHAN
                        </label>
                        <div class="relative">
                            <select 
                                id="bahan" 
                                name="bahan" 
                                class="w-full bg-slate-50 border @error('bahan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            >
                                <option value="" disabled selected>Pilih Bahan</option>
                                <option value="Aluminium / Logam" {{ old('bahan') === 'Aluminium / Logam' ? 'selected' : '' }}>Aluminium / Logam</option>
                                <option value="Besi / Baja" {{ old('bahan') === 'Besi / Baja' ? 'selected' : '' }}>Besi / Baja</option>
                                <option value="Kayu" {{ old('bahan') === 'Kayu' ? 'selected' : '' }}>Kayu</option>
                                <option value="Plastik" {{ old('bahan') === 'Plastik' ? 'selected' : '' }}>Plastik</option>
                                <option value="Kaca" {{ old('bahan') === 'Kaca' ? 'selected' : '' }}>Kaca</option>
                                <option value="Campuran" {{ old('bahan') === 'Campuran' ? 'selected' : '' }}>Campuran</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @error('bahan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tahun Pembelian -->
                    <div>
                        <label for="tahun_pembuatan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            TAHUN PEMBELIAN (YYYY)
                        </label>
                        <input 
                            type="number" 
                            id="tahun_pembuatan" 
                            name="tahun_pembuatan" 
                            value="{{ old('tahun_pembuatan', date('Y')) }}"
                            placeholder="Contoh: 2024"
                            class="w-full bg-slate-50 border @error('tahun_pembuatan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                        @error('tahun_pembuatan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nomor Kode Barang -->
                    <div>
                        <label for="nomor_kode_barang" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            NOMOR KODE BARANG
                        </label>
                        <input 
                            type="text" 
                            id="nomor_kode_barang" 
                            name="nomor_kode_barang" 
                            value="{{ old('nomor_kode_barang') }}"
                            placeholder="KD-AST-000"
                            class="w-full bg-slate-50 border @error('nomor_kode_barang') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                        @error('nomor_kode_barang')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah Barang -->
                    <div>
                        <label for="jumlah" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            JUMLAH BARANG
                        </label>
                        <input 
                            type="number" 
                            id="jumlah" 
                            name="jumlah" 
                            value="{{ old('jumlah', 1) }}"
                            placeholder="1"
                            class="w-full bg-slate-50 border @error('jumlah') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                        @error('jumlah')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Satuan -->
                    <div>
                        <label for="satuan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            SATUAN
                        </label>
                        <div class="relative">
                            <select 
                                id="satuan" 
                                name="satuan" 
                                class="w-full bg-slate-50 border @error('satuan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            >
                                <option value="Unit" {{ old('satuan') === 'Unit' ? 'selected' : '' }}>Unit</option>
                                <option value="Pcs" {{ old('satuan') === 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                <option value="Set" {{ old('satuan') === 'Set' ? 'selected' : '' }}>Set</option>
                                <option value="Meter" {{ old('satuan') === 'Meter' ? 'selected' : '' }}>Meter</option>
                                <option value="Buah" {{ old('satuan') === 'Buah' ? 'selected' : '' }}>Buah</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @error('satuan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keadaan Barang -->
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-3">
                            KEADAAN BARANG
                        </label>
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Baik -->
                            <label class="relative flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer transition-all hover:bg-slate-100/50">
                                <span class="text-xs font-bold text-slate-700">Baik</span>
                                <input 
                                    type="radio" 
                                    name="keadaan_barang" 
                                    value="Baik" 
                                    {{ old('keadaan_barang', 'Baik') === 'Baik' ? 'checked' : '' }}
                                    class="h-4 w-4 text-[#0F3092] border-slate-300 focus:ring-[#0F3092] focus:ring-2"
                                >
                            </label>
                            
                            <!-- Rusak Ringan -->
                            <label class="relative flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer transition-all hover:bg-slate-100/50">
                                <span class="text-xs font-bold text-slate-700">Rusak Ringan</span>
                                <input 
                                    type="radio" 
                                    name="keadaan_barang" 
                                    value="Rusak Ringan" 
                                    {{ old('keadaan_barang') === 'Rusak Ringan' ? 'checked' : '' }}
                                    class="h-4 w-4 text-[#0F3092] border-slate-300 focus:ring-[#0F3092] focus:ring-2"
                                >
                            </label>
                            
                            <!-- Rusak Berat -->
                            <label class="relative flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer transition-all hover:bg-slate-100/50">
                                <span class="text-xs font-bold text-slate-700">Rusak Berat</span>
                                <input 
                                    type="radio" 
                                    name="keadaan_barang" 
                                    value="Rusak Berat" 
                                    {{ old('keadaan_barang') === 'Rusak Berat' ? 'checked' : '' }}
                                    class="h-4 w-4 text-[#0F3092] border-slate-300 focus:ring-[#0F3092] focus:ring-2"
                                >
                            </label>
                        </div>
                        @error('keadaan_barang')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ruangan -->
                    <div>
                        <label for="ruangan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            RUANGAN
                        </label>
                        <div class="relative">
                            <select 
                                id="ruangan" 
                                name="ruangan" 
                                class="w-full bg-slate-50 border @error('ruangan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            >
                                <option value="" disabled selected>Pilih Ruangan</option>
                                <option value="Ruang Server Utama" {{ old('ruangan') === 'Ruang Server Utama' ? 'selected' : '' }}>Ruang Server Utama</option>
                                <option value="Ruang Staff Persandian" {{ old('ruangan') === 'Ruang Staff Persandian' ? 'selected' : '' }}>Ruang Staff Persandian</option>
                                <option value="Ruang Bidang IKP" {{ old('ruangan') === 'Ruang Bidang IKP' ? 'selected' : '' }}>Ruang Bidang IKP</option>
                                <option value="Ruang Kepala Dinas" {{ old('ruangan') === 'Ruang Kepala Dinas' ? 'selected' : '' }}>Ruang Kepala Dinas</option>
                                <option value="Gudang Aset" {{ old('ruangan') === 'Gudang Aset' ? 'selected' : '' }}>Gudang Aset</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @error('ruangan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Penanggung Jawab -->
                    <div>
                        <label for="penanggung_jawab" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            PENANGGUNG JAWAB
                        </label>
                        <input 
                            type="text" 
                            id="penanggung_jawab" 
                            name="penanggung_jawab" 
                            value="{{ old('penanggung_jawab') }}"
                            placeholder="Nama Lengkap PJ"
                            class="w-full bg-slate-50 border @error('penanggung_jawab') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >
                        @error('penanggung_jawab')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KETERANGAN
                        </label>
                        <textarea 
                            id="keterangan" 
                            name="keterangan" 
                            rows="4"
                            placeholder="Tambahkan catatan tambahan di sini..."
                            class="w-full bg-slate-50 border @error('keterangan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium"
                        >{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                    <button type="reset" class="text-slate-500 hover:text-slate-700 text-xs font-bold uppercase tracking-wider transition-colors">
                        Reset Form
                    </button>
                    
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin-perbidang.data-aset-smki.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm">
                            Simpan Aset Baru
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar Guides/Cards (Spans 1 column) -->
        <div class="space-y-6">
            <!-- Data Terintegrasi Card -->
            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">
                    DATA TERINTEGRASI
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Input ini akan otomatis tercatat dalam Audit SMKI Tahunan sesuai kebijakan divisi.
                </p>
            </div>

            <!-- Panduan Input Card -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">
                    PANDUAN INPUT
                </h4>
                
                <div class="space-y-3">
                    <div class="flex items-start space-x-3 text-xs text-slate-600">
                        <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold flex-shrink-0 text-[10px]">
                            i
                        </div>
                        <p class="leading-relaxed">
                            Pastikan Nomor Seri sesuai dengan yang tertera pada fisik barang.
                        </p>
                    </div>

                    <div class="flex items-start space-x-3 text-xs text-slate-600">
                        <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold flex-shrink-0 text-[10px]">
                            ✓
                        </div>
                        <p class="leading-relaxed">
                            Gunakan format YYYY untuk kolom Tahun Pembelian.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('create-asset-form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (this.getAttribute('data-confirmed') === 'true') {
                        return;
                    }
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Simpan',
                        text: 'Apakah Anda yakin data aset yang dimasukkan sudah benar?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#002D84',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, Simpan!',
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
