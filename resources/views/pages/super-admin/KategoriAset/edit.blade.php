<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Edit Kategori Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Perbarui nama, tipe, atau deskripsi kategori aset.
            </p>
        </div>

        <a href="{{ route('super-admin.kategori-aset.index') }}" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center transition-all duration-150 shadow-sm">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center space-x-2">
                    <span class="h-4 w-1 bg-[#0F3092] rounded inline-block"></span>
                    <span>{{ $category->nama_kategori }}</span>
                </h3>
            </div>

            <form action="{{ route('super-admin.kategori-aset.update', $category->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tipe" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            Tipe Kategori
                        </label>
                        <select
                            id="tipe"
                            name="tipe"
                            class="w-full bg-slate-50 border @error('tipe') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                            required
                        >
                            <option value="Register" {{ old('tipe', $category->tipe) === 'Register' ? 'selected' : '' }}>Register</option>
                            <option value="SMKI" {{ old('tipe', $category->tipe) === 'SMKI' ? 'selected' : '' }}>SMKI</option>
                        </select>
                        @error('tipe')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_kategori" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            Nama Kategori
                        </label>
                        <input
                            type="text"
                            id="nama_kategori"
                            name="nama_kategori"
                            value="{{ old('nama_kategori', $category->nama_kategori) }}"
                            class="w-full bg-slate-50 border @error('nama_kategori') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                            required
                        >
                        @error('nama_kategori')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="deskripsi" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            Deskripsi
                        </label>
                        <textarea
                            id="deskripsi"
                            name="deskripsi"
                            rows="4"
                            class="w-full bg-slate-50 border @error('deskripsi') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3 focus:outline-none transition-colors font-medium"
                        >{{ old('deskripsi', $category->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 border-t border-slate-100 pt-6">
                    <a href="{{ route('super-admin.kategori-aset.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors text-center">
                        Batal
                    </a>
                    <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl transition-all duration-150 shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-3">
            <span class="text-[9px] font-bold text-slate-500 tracking-wider uppercase block">
                Pemakaian Kategori
            </span>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight">
                {{ number_format($usageCount) }}
            </p>
            <p class="text-slate-600 text-xs font-medium leading-relaxed">
                Kategori yang sedang digunakan aset tidak dapat dihapus dari halaman daftar.
            </p>
        </div>
    </div>
</x-app-layout>
