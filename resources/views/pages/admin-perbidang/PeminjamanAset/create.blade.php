<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Pengajuan Peminjaman Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Isi data peminjaman untuk dikirim ke Super Admin.
            </p>
        </div>

        <a href="{{ route('admin-perbidang.peminjaman-aset.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="h-4 w-1 bg-[#0F3092] rounded inline-block"></span>
                    <span>Form Peminjaman Aset</span>
                </h3>
            </div>

            <form action="{{ route('admin-perbidang.peminjaman-aset.store') }}" method="POST" id="peminjaman-form" class="space-y-6">
                @csrf
                <input type="hidden" name="jenis_aset" id="jenis_aset" value="{{ old('jenis_aset') }}">
                <input type="hidden" name="aset_id" id="aset_id" value="{{ old('aset_id') }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="bidang_asal_id" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            LOKASI ASAL ASET (BIDANG)
                        </label>
                        <select
                            id="bidang_asal_id"
                            name="bidang_asal_id"
                            class="w-full bg-slate-50 border @error('bidang_asal_id') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            required
                        >
                            <option value="" disabled {{ old('bidang_asal_id') ? '' : 'selected' }}>Pilih bidang asal aset</option>
                            @foreach($bidangs as $bidang)
                                <option value="{{ $bidang->id }}" {{ (string) old('bidang_asal_id') === (string) $bidang->id ? 'selected' : '' }}>
                                    {{ $bidang->nama_bidang }}
                                </option>
                            @endforeach
                        </select>
                        @error('bidang_asal_id')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_peminjam" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            NAMA PEMINJAM
                        </label>
                        <input
                            type="text"
                            id="nama_peminjam"
                            name="nama_peminjam"
                            value="{{ old('nama_peminjam') }}"
                            placeholder="Masukkan nama peminjam"
                            class="w-full bg-slate-50 border @error('nama_peminjam') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                            required
                        >
                        @error('nama_peminjam')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="asset_selector" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            ASET YANG DIPINJAM
                        </label>
                        <select
                            id="asset_selector"
                            class="w-full bg-slate-50 border @if($errors->has('jenis_aset') || $errors->has('aset_id')) border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @endif text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            required
                        >
                            <option value="" disabled selected>Pilih aset terverifikasi yang tersedia</option>
                            @foreach($assets as $asset)
                                <option
                                    value="{{ $asset->type }}:{{ $asset->id }}"
                                    data-bidang-id="{{ $asset->bidang_id }}"
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
                        <label for="tanggal_pinjam" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            TANGGAL PINJAM
                        </label>
                        <input
                            type="date"
                            id="tanggal_pinjam"
                            name="tanggal_pinjam"
                            value="{{ old('tanggal_pinjam', now()->toDateString()) }}"
                            class="w-full bg-slate-50 border @error('tanggal_pinjam') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                            required
                        >
                        @error('tanggal_pinjam')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_rencana_kembali" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            RENCANA KEMBALI
                        </label>
                        <input
                            type="date"
                            id="tanggal_rencana_kembali"
                            name="tanggal_rencana_kembali"
                            value="{{ old('tanggal_rencana_kembali', now()->addDays(7)->toDateString()) }}"
                            class="w-full bg-slate-50 border @error('tanggal_rencana_kembali') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                            required
                        >
                        @error('tanggal_rencana_kembali')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="keperluan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KEPERLUAN PEMINJAMAN
                        </label>
                        <textarea
                            id="keperluan"
                            name="keperluan"
                            rows="5"
                            placeholder="Jelaskan keperluan peminjaman aset..."
                            class="w-full bg-slate-50 border @error('keperluan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium"
                            required
                        >{{ old('keperluan') }}</textarea>
                        @error('keperluan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="catatan" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            CATATAN TAMBAHAN
                        </label>
                        <textarea
                            id="catatan"
                            name="catatan"
                            rows="3"
                            placeholder="Tambahkan catatan jika diperlukan..."
                            class="w-full bg-slate-50 border @error('catatan') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium"
                        >{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 border-t border-slate-100 pt-6">
                    <a href="{{ route('admin-perbidang.peminjaman-aset.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors">
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
                    Pengajuan akan masuk ke Super Admin dengan status Menunggu Verifikasi. Status aset berubah menjadi Dipinjam setelah pengajuan disetujui pada tahap verifikasi.
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
                    Hanya aset terverifikasi yang belum memiliki peminjaman aktif yang dapat dipilih.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const assetSelector = document.getElementById('asset_selector');
            const bidangAsal = document.getElementById('bidang_asal_id');
            const jenisAset = document.getElementById('jenis_aset');
            const asetId = document.getElementById('aset_id');
            const form = document.getElementById('peminjaman-form');

            function filterAssetsByBidang() {
                const selectedBidang = bidangAsal.value;
                const currentValue = assetSelector.value;
                let currentValueStillVisible = false;

                Array.from(assetSelector.options).forEach((option) => {
                    if (!option.value) {
                        option.textContent = selectedBidang ? 'Pilih aset terverifikasi yang tersedia' : 'Pilih lokasi asal aset terlebih dahulu';
                        return;
                    }

                    const isVisible = selectedBidang && option.dataset.bidangId === selectedBidang;
                    option.hidden = !isVisible;
                    option.disabled = !isVisible;

                    if (isVisible && option.value === currentValue) {
                        currentValueStillVisible = true;
                    }
                });

                assetSelector.disabled = !selectedBidang;

                if (!currentValueStillVisible) {
                    assetSelector.value = '';
                }

                syncAssetFields();
            }

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

            filterAssetsByBidang();
            bidangAsal.addEventListener('change', filterAssetsByBidang);
            assetSelector.addEventListener('change', syncAssetFields);

            form.addEventListener('submit', function (event) {
                syncAssetFields();
                if (this.getAttribute('data-confirmed') === 'true') {
                    return;
                }

                event.preventDefault();
                Swal.fire({
                    title: 'Kirim Pengajuan Peminjaman',
                    text: 'Pastikan data peminjaman aset sudah benar sebelum dikirim.',
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
