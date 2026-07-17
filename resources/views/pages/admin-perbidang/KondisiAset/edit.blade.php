<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            Perbarui Kondisi Aset
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Ubah status kondisi fisik untuk aset spesifik di bawah ini.
        </p>
    </div>

    <!-- Main Grid: Form and Side Guides -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Form Container (Spans 2 columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="h-4 w-1 bg-[#0F3092] rounded inline-block"></span>
                    <span>Form Pembaruan Kondisi Aset</span>
                </h3>
                <span class="bg-slate-50 border border-slate-200 text-slate-400 text-[10px] font-bold px-2.5 py-1 rounded-md">
                    UPDATE KONDISI
                </span>
            </div>

            <form action="{{ route('admin-perbidang.kondisi-aset.update', $assetData->id) }}" method="POST" enctype="multipart/form-data" id="edit-kondisi-form" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Hidden inputs to identify the asset and its type -->
                <input type="hidden" name="tipe_aset" value="{{ $assetData->category }}" />

                <!-- Form Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Aset (Read-Only) -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            NAMA ASET
                        </label>
                        <input 
                            type="text" 
                            disabled 
                            value="{{ $assetData->name }}"
                            class="w-full bg-slate-100 border border-slate-200 text-slate-500 text-xs rounded-xl px-4 py-3.5 focus:outline-none font-semibold cursor-not-allowed"
                        />
                    </div>

                    <!-- Kode Aset (Read-Only) -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KODE ASET
                        </label>
                        <input 
                            type="text" 
                            disabled 
                            value="{{ $assetData->code }}"
                            class="w-full bg-slate-100 border border-slate-200 text-slate-500 text-xs rounded-xl px-4 py-3.5 focus:outline-none font-semibold cursor-not-allowed"
                        />
                    </div>

                    <!-- Kategori & Kondisi Saat Ini (Read-Only) -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KATEGORI
                        </label>
                        <div class="py-3">
                            @if($assetData->category === 'REGISTER')
                                <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded border border-blue-200 tracking-wide uppercase">
                                    REGISTER
                                </span>
                            @else
                                <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2.5 py-1 rounded border border-slate-200 tracking-wide uppercase">
                                    SMKI
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KONDISI SAAT INI
                        </label>
                        <div class="py-2.5 flex items-center">
                            @php
                                switch ($assetData->condition) {
                                    case 'Baik':
                                        $dotColor = 'bg-emerald-500';
                                        break;
                                    case 'Rusak Ringan':
                                        $dotColor = 'bg-amber-500';
                                        break;
                                    case 'Rusak Berat':
                                    default:
                                        $dotColor = 'bg-rose-500';
                                        break;
                                }
                            @endphp
                            <span class="inline-flex items-center font-bold text-sm text-slate-700">
                                <span class="mr-2 h-2.5 w-2.5 rounded-full {{ $dotColor }} border border-white shadow-sm"></span>
                                {{ $assetData->condition }}
                            </span>
                        </div>
                    </div>

                    <!-- Kondisi Baru -->
                    <div class="md:col-span-2 border-t border-slate-100 pt-4">
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-3">
                            KONDISI BARU
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
                                    {{ old('keadaan_baru', $assetData->condition) === 'Baik' ? 'checked' : '' }}
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
                                    {{ old('keadaan_baru', $assetData->condition) === 'Rusak Ringan' ? 'checked' : '' }}
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
                                    {{ old('keadaan_baru', $assetData->condition) === 'Rusak Berat' ? 'checked' : '' }}
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
                            UPLOAD FOTO KONDISI TERBARU
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

                    <!-- Catatan Tambahan -->
                    <div class="md:col-span-2">
                        <label for="catatan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            CATATAN PERUBAHAN / TINDAKAN PERBAIKAN
                        </label>
                        <textarea 
                            id="catatan" 
                            name="catatan" 
                            rows="4"
                            required
                            maxlength="1000"
                            placeholder="Sebutkan detail kondisi terkini, tindakan perbaikan, atau catatan pemeliharaan yang relevan..."
                            class="w-full bg-slate-50 border @error('catatan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                        >{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                    <div></div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin-perbidang.kondisi-aset.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm">
                            Perbarui Kondisi
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar Guides/Cards (Spans 1 column) -->
        <div class="space-y-6">
            <!-- Informasi Aset Card -->
            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">
                    PANDUAN UPDATE KONDISI
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Anda sedang memperbarui status untuk aset <strong>{{ $assetData->name }}</strong>. Perubahan ini akan memicu status operasional aset berubah secara otomatis di database.
                </p>
            </div>

            <!-- Panduan Input Card -->
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">
                    ATURAN STATUS OPERASIONAL
                </h4>
                
                <div class="space-y-3 text-xs text-slate-600 leading-relaxed">
                    <div>
                        <strong class="text-slate-800 font-semibold">Baik:</strong> Aset ditandai sebagai <span class="text-emerald-600 font-bold">Tersedia</span> dan siap digunakan.
                    </div>
                    <div class="border-t border-slate-200/60 pt-2.5">
                        <strong class="text-slate-800 font-semibold">Rusak Ringan:</strong> Aset ditandai masuk status <span class="text-amber-600 font-bold">Maintenance</span> (perbaikan/servis).
                    </div>
                    <div class="border-t border-slate-200/60 pt-2.5">
                        <strong class="text-slate-800 font-semibold">Rusak Berat:</strong> Aset ditandai masuk status <span class="text-rose-600 font-bold">Rusak</span> dan dipersiapkan untuk proses penghapusan aset.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS for submit alert confirmation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('edit-kondisi-form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (this.getAttribute('data-confirmed') === 'true') {
                        return;
                    }
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Perubahan',
                        text: 'Apakah Anda yakin ingin memperbarui kondisi aset ini?',
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
