<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Pengajuan Mutasi Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Isi data perpindahan aset untuk dikirim ke Super Admin.
            </p>
        </div>

        <a href="{{ route('admin-perbidang.mutasi-aset.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="h-4 w-1 bg-[#0F3092] rounded inline-block"></span>
                    <span>Form Mutasi Aset</span>
                </h3>
            </div>

            <form action="{{ route('admin-perbidang.mutasi-aset.store') }}" method="POST" id="mutasi-form" class="space-y-6">
                @csrf
                <input type="hidden" name="jenis_aset" id="jenis_aset" value="{{ old('jenis_aset') }}">
                <input type="hidden" name="aset_id" id="aset_id" value="{{ old('aset_id') }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="asset_selector" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            ASET YANG DIMUTASIKAN
                        </label>
                        <select
                            id="asset_selector"
                            class="w-full bg-slate-50 border @if($errors->has('jenis_aset') || $errors->has('aset_id')) border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @endif text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            required
                        >
                            <option value="" disabled selected>Pilih aset terverifikasi</option>
                            @foreach($assets as $asset)
                                <option
                                    value="{{ $asset->type }}:{{ $asset->id }}"
                                    {{ old('jenis_aset') === $asset->type && (string) old('aset_id') === (string) $asset->id ? 'selected' : '' }}
                                >
                                    {{ $asset->label }}
                                </option>
                            @endforeach
                        </select>
                        @if($errors->has('jenis_aset') || $errors->has('aset_id'))
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">
                                {{ $errors->first('jenis_aset') ?: $errors->first('aset_id') }}
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            BIDANG ASAL
                        </label>
                        <input
                            type="text"
                            value="{{ auth()->user()->bidang->nama_bidang ?? '-' }}"
                            disabled
                            class="w-full bg-slate-100 border border-slate-200 text-slate-500 text-xs rounded-xl px-4 py-3.5 font-medium cursor-not-allowed"
                        >
                    </div>

                    <div>
                        <label for="bidang_tujuan_id" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            BIDANG TUJUAN
                        </label>
                        <select
                            id="bidang_tujuan_id"
                            name="bidang_tujuan_id"
                            class="w-full bg-slate-50 border @error('bidang_tujuan_id') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            required
                        >
                            <option value="" disabled {{ old('bidang_tujuan_id') ? '' : 'selected' }}>Pilih bidang tujuan</option>
                            @foreach($bidangs as $bidang)
                                <option value="{{ $bidang->id }}" {{ (string) old('bidang_tujuan_id') === (string) $bidang->id ? 'selected' : '' }}>
                                    {{ $bidang->nama_bidang }}
                                </option>
                            @endforeach
                        </select>
                        @error('bidang_tujuan_id')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_mutasi" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            TANGGAL MUTASI
                        </label>
                        <input
                            type="date"
                            id="tanggal_mutasi"
                            name="tanggal_mutasi"
                            value="{{ old('tanggal_mutasi', now()->toDateString()) }}"
                            class="w-full bg-slate-50 border @error('tanggal_mutasi') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                            required
                        >
                        @error('tanggal_mutasi')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="alasan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            ALASAN MUTASI
                        </label>
                        <textarea
                            id="alasan"
                            name="alasan"
                            rows="5"
                            placeholder="Jelaskan alasan perpindahan aset..."
                            class="w-full bg-slate-50 border @error('alasan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium"
                            required
                        >{{ old('alasan') }}</textarea>
                        @error('alasan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 border-t border-slate-100 pt-6">
                    <a href="{{ route('admin-perbidang.mutasi-aset.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6 space-y-3">
                <span class="text-[9px] font-bold text-blue-600 tracking-wider uppercase block">
                    Alur Persetujuan
                </span>
                <p class="text-slate-600 text-xs font-medium leading-relaxed">
                    Pengajuan akan masuk ke Super Admin dengan status Menunggu Verifikasi. Perubahan bidang aset dilakukan setelah pengajuan disetujui.
                </p>
            </div>

            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-3">
                <h4 class="text-xs font-bold text-slate-800 tracking-wider uppercase">
                    Aset Tersedia
                </h4>
                <p class="text-sm font-extrabold text-slate-800">
                    {{ $assets->count() }} Aset
                </p>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Hanya aset terverifikasi dari bidang Anda yang dapat diajukan mutasi.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const assetSelector = document.getElementById('asset_selector');
            const jenisAset = document.getElementById('jenis_aset');
            const asetId = document.getElementById('aset_id');
            const form = document.getElementById('mutasi-form');

            function syncAssetFields() {
                const value = assetSelector.value;
                if (!value.includes(':')) {
                    jenisAset.value = '';
                    asetId.value = '';
                    return;
                }

                const [type, id] = value.split(':');
                jenisAset.value = type;
                asetId.value = id;
            }

            syncAssetFields();
            assetSelector.addEventListener('change', syncAssetFields);

            form.addEventListener('submit', function (event) {
                syncAssetFields();
                if (this.getAttribute('data-confirmed') === 'true') {
                    return;
                }

                event.preventDefault();
                Swal.fire({
                    title: 'Kirim Pengajuan Mutasi',
                    text: 'Pastikan data mutasi aset sudah benar sebelum dikirim.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#002D84',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.setAttribute('data-confirmed', 'true');
                        this.submit();
                    }
                });
            });
        });
    </script>
</x-app-layout>
