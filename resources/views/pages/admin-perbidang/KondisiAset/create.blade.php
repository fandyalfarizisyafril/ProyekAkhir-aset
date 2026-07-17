<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            Catat Riwayat Kondisi Aset
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Silakan pilih aset dan masukkan detail pembaruan kondisi fisik aset.
        </p>
    </div>

    <!-- Main Grid: Form and Side Guides -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Form Container (Spans 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="h-4 w-1 bg-[#0F3092] rounded inline-block"></span>
                    <span>Form Riwayat Kondisi Aset</span>
                </h3>
                <span class="bg-slate-50 border border-slate-200 text-slate-400 text-[10px] font-bold px-2.5 py-1 rounded-md">
                    RIWAYAT BARU
                </span>
            </div>

            <form action="{{ route('admin-perbidang.kondisi-aset.store') }}" method="POST" enctype="multipart/form-data" id="create-kondisi-form" class="space-y-6">
                @csrf

                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipe Aset (SMKI / REGISTER) -->
                    <div>
                        <label for="tipe_aset" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            TIPE ASET
                        </label>
                        <div class="relative">
                            <select 
                                id="tipe_aset" 
                                name="tipe_aset" 
                                required
                                class="w-full bg-slate-50 border @error('tipe_aset') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            >
                                <option value="REGISTER" {{ old('tipe_aset', $selectedType) === 'REGISTER' ? 'selected' : '' }}>Aset Register (Peralatan & Mesin, Gedung, dll)</option>
                                <option value="SMKI" {{ old('tipe_aset', $selectedType) === 'SMKI' ? 'selected' : '' }}>Aset SMKI (Server, Network, Lisensi, dll)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @error('tipe_aset')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pilih Aset -->
                    <div>
                        <label for="aset_id" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            PILIH ASET
                        </label>
                        <div class="relative">
                            <select 
                                id="aset_id" 
                                name="aset_id" 
                                required
                                class="w-full bg-slate-50 border @error('aset_id') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            >
                                <option value="" disabled selected>Pilih Aset...</option>
                                <!-- Populate Register Assets -->
                                @foreach($registerAssets as $regAsset)
                                    <option value="{{ $regAsset->id }}" data-type="REGISTER" {{ old('aset_id', $selectedId) == $regAsset->id && old('tipe_aset', $selectedType) === 'REGISTER' ? 'selected' : '' }}>
                                        {{ $regAsset->nama_aset }} ({{ $regAsset->kode_aset }}) - {{ $regAsset->kondisi }}
                                    </option>
                                @endforeach
                                <!-- Populate SMKI Assets -->
                                @foreach($smkiAssets as $smkiAsset)
                                    <option value="{{ $smkiAsset->id }}" data-type="SMKI" {{ old('aset_id', $selectedId) == $smkiAsset->id && old('tipe_aset', $selectedType) === 'SMKI' ? 'selected' : '' }}>
                                        {{ $smkiAsset->merk_model }} ({{ $smkiAsset->nomor_kode_barang }}) - {{ $smkiAsset->keadaan_barang }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @error('aset_id')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kondisi Baru -->
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-3">
                            KONDISI BARU ASET
                        </label>
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Baik -->
                            <label class="relative flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer transition-all hover:bg-slate-100/50">
                                <div class="flex items-center space-x-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                    <span class="text-xs font-bold text-slate-700">Baik</span>
                                </div>
                                <input 
                                    type="radio" 
                                    name="keadaan_baru" 
                                    value="Baik" 
                                    {{ old('keadaan_baru', 'Baik') === 'Baik' ? 'checked' : '' }}
                                    required
                                    class="h-4 w-4 text-[#0F3092] border-slate-300 focus:ring-[#0F3092] focus:ring-2"
                                />
                            </label>
                            
                            <!-- Rusak Ringan -->
                            <label class="relative flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer transition-all hover:bg-slate-100/50">
                                <div class="flex items-center space-x-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500 inline-block"></span>
                                    <span class="text-xs font-bold text-slate-700">Rusak Ringan</span>
                                </div>
                                <input 
                                    type="radio" 
                                    name="keadaan_baru" 
                                    value="Rusak Ringan" 
                                    {{ old('keadaan_baru') === 'Rusak Ringan' ? 'checked' : '' }}
                                    required
                                    class="h-4 w-4 text-[#0F3092] border-slate-300 focus:ring-[#0F3092] focus:ring-2"
                                />
                            </label>
                            
                            <!-- Rusak Berat -->
                            <label class="relative flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer transition-all hover:bg-slate-100/50">
                                <div class="flex items-center space-x-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-rose-500 inline-block"></span>
                                    <span class="text-xs font-bold text-slate-700">Rusak Berat</span>
                                </div>
                                <input 
                                    type="radio" 
                                    name="keadaan_baru" 
                                    value="Rusak Berat" 
                                    {{ old('keadaan_baru') === 'Rusak Berat' ? 'checked' : '' }}
                                    required
                                    class="h-4 w-4 text-[#0F3092] border-slate-300 focus:ring-[#0F3092] focus:ring-2"
                                />
                            </label>
                        </div>
                        @error('keadaan_baru')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Upload Foto Pendukung -->
                    <div class="md:col-span-2">
                        <label for="foto" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            UPLOAD FOTO KONDISI ASET
                        </label>
                        <input 
                            type="file" 
                            id="foto" 
                            name="foto" 
                            accept="image/jpeg,image/png,image/webp"
                            required
                            class="w-full bg-slate-50 border @error('foto') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-500 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium file:mr-4 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300"
                        />
                        <p class="text-slate-400 text-[10px] mt-1.5">Wajib diunggah. Maksimal 2MB. Format: JPEG, JPG, PNG, atau WEBP.</p>
                        @error('foto')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Tambahan / Keterangan -->
                    <div class="md:col-span-2">
                        <label for="catatan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            CATATAN PERBAIKAN / KONDISI
                        </label>
                        <textarea 
                            id="catatan" 
                            name="catatan" 
                            rows="4"
                            required
                            maxlength="1000"
                            placeholder="Tulis alasan perubahan kondisi, detail kerusakan, atau tindakan perbaikan yang dilakukan..."
                            class="w-full bg-slate-50 border @error('catatan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >{{ old('catatan') }}</textarea>
                        @error('catatan')
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
                        <a href="{{ route('admin-perbidang.kondisi-aset.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm">
                            Simpan Riwayat
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
                    PENCATATAN RIWAYAT
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Setiap pembaruan kondisi akan mencatat status lama dan status baru, serta menyimpan data petugas yang melakukan perubahan untuk audit internal.
                </p>
            </div>

            <!-- Panduan Input Card -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">
                    PANDUAN PENGISIAN
                </h4>
                
                <div class="space-y-3">
                    <div class="flex items-start space-x-3 text-xs text-slate-600">
                        <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold flex-shrink-0 text-[10px]">
                            1
                        </div>
                        <p class="leading-relaxed">
                            Pilih tipe aset terlebih dahulu untuk menyaring daftar aset di sebelahnya.
                        </p>
                    </div>

                    <div class="flex items-start space-x-3 text-xs text-slate-600">
                        <div class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold flex-shrink-0 text-[10px]">
                            2
                        </div>
                        <p class="leading-relaxed">
                            Pastikan menyertakan foto kondisi fisik yang jelas, terutama untuk kondisi <strong>Rusak Ringan</strong> atau <strong>Rusak Berat</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS for dynamic asset filtering and alert confirmation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipeSelect = document.getElementById('tipe_aset');
            const asetSelect = document.getElementById('aset_id');
            const form = document.getElementById('create-kondisi-form');

            // Save all original options in an array
            const allOptions = Array.from(asetSelect.options).filter(opt => opt.value !== "");

            function filterAssets() {
                const selectedType = tipeSelect.value;

                // Clear current options except placeholder
                asetSelect.innerHTML = '<option value="" disabled selected>Pilih Aset...</option>';

                // Add options matching selectedType
                allOptions.forEach(opt => {
                    if (opt.getAttribute('data-type') === selectedType) {
                        // Create a clone option
                        const clone = opt.cloneNode(true);
                        asetSelect.appendChild(clone);
                    }
                });

                // Auto-select old or preselected values
                const preselectedId = "{{ old('aset_id', $selectedId) }}";
                const preselectedType = "{{ old('tipe_aset', $selectedType) }}";
                
                if (preselectedId && selectedType === preselectedType) {
                    asetSelect.value = preselectedId;
                }
            }

            // Bind change event
            tipeSelect.addEventListener('change', filterAssets);

            // Initial filtering
            filterAssets();

            // Form Submit confirmation
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (this.getAttribute('data-confirmed') === 'true') {
                        return;
                    }
                    e.preventDefault();

                    const assetVal = asetSelect.value;
                    if (!assetVal) {
                        Swal.fire({
                            title: 'Peringatan!',
                            text: 'Harap pilih aset yang akan diupdate terlebih dahulu.',
                            icon: 'warning',
                            confirmButtonColor: '#002D84'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Konfirmasi Simpan',
                        text: 'Apakah Anda yakin ingin mencatat riwayat kondisi aset ini?',
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
