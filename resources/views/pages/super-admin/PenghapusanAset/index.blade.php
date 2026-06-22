<x-app-layout>
    @php
        $formatNumber = fn ($value) => number_format((int) $value, 0, ',', '.');
        $formatCurrency = fn ($value) => $value === null ? '-' : 'Rp ' . number_format((float) $value, 0, ',', '.');
        $methods = ['Pemusnahan', 'Penjualan', 'Hibah', 'Pengalihan', 'Lainnya'];
    @endphp

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Penghapusan Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Nonaktifkan aset terverifikasi dari inventaris aktif dengan riwayat penghapusan yang tercatat.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($viewMode === 'riwayat')
                <a href="{{ route('super-admin.penghapusan-aset.index', $filters) }}" class="inline-flex items-center gap-2 bg-[#0F3092] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-4 py-3 rounded-xl transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Daftar Aset Aktif
                </a>
            @else
                <a href="{{ route('super-admin.penghapusan-aset.index', array_merge($filters, ['view' => 'riwayat'])) }}" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-[#0F3092] border border-slate-200 text-xs font-bold uppercase tracking-wider px-4 py-3 rounded-xl transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Penghapusan
                </a>
            @endif
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

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center space-x-3 text-rose-800 text-sm shadow-sm">
            <svg class="h-5 w-5 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zm8.25-4.5a8.25 8.25 0 11-16.5 0 8.25 8.25 0 0116.5 0z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-800 text-sm shadow-sm">
            <div class="font-bold mb-1">Data penghapusan belum lengkap.</div>
            <div class="text-xs font-medium">{{ $errors->first() }}</div>
        </div>
    @endif

    @if($viewMode === 'aktif')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 mb-8">
        <form action="{{ route('super-admin.penghapusan-aset.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label for="jenis" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                    Jenis Aset
                </label>
                <select id="jenis" name="jenis" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>Register</option>
                    <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
                </select>
            </div>

            <div>
                <label for="bidang_id" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                    Bidang
                </label>
                <select id="bidang_id" name="bidang_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                    @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>
                            {{ $bidang->nama_bidang }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-3">
                <label for="search" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                    Cari Aset
                </label>
                <div class="relative">
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Cari nama aset, kode aset, kategori, atau kondisi..."
                        class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl pl-10 pr-10 py-3 focus:outline-none focus:border-[#0F3092] transition-colors font-medium"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#0F3092]" title="Cari">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">
                    Daftar Aset Aktif
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Hanya aset terverifikasi dan belum dihapus yang tampil di daftar ini.
                </p>
            </div>
        </div>

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Aset</th>
                        <th class="py-4 px-4">Jenis</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Kondisi</th>
                        <th class="py-4 px-4">Nilai Buku</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 min-w-[280px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors align-top">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $asset->name }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    <span class="font-semibold text-slate-600">{{ $asset->code }}</span>
                                    <span class="px-1">|</span>
                                    <span>{{ $asset->category }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 {{ $asset->type === 'register' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    {{ $asset->type_label }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ $asset->bidang->nama_bidang ?? '-' }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-semibold {{ $asset->is_damaged ? 'text-rose-600' : 'text-slate-600' }}">
                                    {{ $asset->condition }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">
                                {{ $formatCurrency($asset->book_value) }}
                                @if($asset->latest_depreciation_year)
                                    <div class="text-[10px] text-slate-400 font-medium mt-1">
                                        Penyusutan {{ $asset->latest_depreciation_year }}
                                    </div>
                                @elseif($asset->type === 'register')
                                    <div class="text-[10px] text-slate-400 font-medium mt-1">
                                        Nilai perolehan
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($asset->has_active_loan)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-amber-50 text-amber-700 border border-amber-200">
                                        Dipinjam
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-slate-50 text-slate-600 border border-slate-200">
                                        {{ $asset->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($asset->has_active_loan)
                                    <div class="text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                                        Selesaikan peminjaman aktif sebelum penghapusan.
                                    </div>
                                @else
                                    <details class="group">
                                        <summary class="cursor-pointer inline-flex items-center justify-center bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold uppercase tracking-wider px-4 py-2.5 rounded-xl transition-colors">
                                            Nonaktifkan
                                        </summary>
                                        <form action="{{ route('super-admin.penghapusan-aset.store', [$asset->type, $asset->id]) }}" method="POST" class="mt-3 space-y-3 rounded-2xl border border-rose-100 bg-rose-50/30 p-3 delete-asset-form" data-asset-name="{{ $asset->name }}">
                                            @csrf
                                            <input type="hidden" name="jenis" value="{{ $filters['jenis'] }}">
                                            <input type="hidden" name="bidang_id" value="{{ $filters['bidang_id'] }}">
                                            <input type="hidden" name="search" value="{{ $filters['search'] }}">

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal</label>
                                                    <input type="date" name="tanggal_penghapusan" value="{{ old('tanggal_penghapusan', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#0F3092]">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Metode</label>
                                                    <select name="metode_penghapusan" required class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#0F3092]">
                                                        @foreach($methods as $method)
                                                            <option value="{{ $method }}" {{ old('metode_penghapusan') === $method ? 'selected' : '' }}>{{ $method }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alasan</label>
                                                <textarea name="alasan" rows="2" maxlength="1000" required placeholder="Contoh: rusak berat dan tidak ekonomis diperbaiki" class="w-full bg-white border border-slate-200 text-slate-700 text-xs rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#0F3092]">{{ old('alasan') }}</textarea>
                                            </div>

                                            <button type="submit" style="background-color: #E11D48; color: #FFFFFF;" class="w-full border border-rose-700 hover:bg-rose-700 text-[11px] font-bold uppercase tracking-wider px-4 py-3 rounded-xl transition-colors shadow-sm">
                                                Simpan Penghapusan
                                            </button>
                                        </form>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada aset terverifikasi yang cocok dengan filter penghapusan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($assets->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} aset
                </div>
                <div>
                    {{ $assets->links() }}
                </div>
            </div>
        @endif
    </div>
    @else

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">
                    Riwayat Penghapusan Terbaru
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Catatan ini tetap tersimpan sebagai audit trail penghapusan aset.
                </p>
            </div>
            <span class="inline-flex items-center self-start rounded-full bg-slate-50 border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600">
                {{ $history->count() }} Riwayat
            </span>
        </div>

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Tanggal</th>
                        <th class="py-4 px-4">Aset</th>
                        <th class="py-4 px-4">Jenis</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Nilai Buku</th>
                        <th class="py-4 px-4">Metode</th>
                        <th class="py-4 px-4">Dihapus Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($history as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ $item->tanggal_penghapusan->format('d M Y') }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $item->nama_aset }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">{{ $item->kode_aset }}</div>
                                <div class="text-[10px] text-slate-500 mt-1 max-w-md">{{ $item->alasan }}</div>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ strtoupper($item->jenis_aset) }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ $item->bidang->nama_bidang ?? '-' }}
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-800">
                                {{ $formatCurrency($item->nilai_buku) }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ $item->metode_penghapusan }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ $item->remover->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Belum ada riwayat penghapusan aset.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-asset-form').forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'Nonaktifkan Aset?',
                        text: `Aset "${this.getAttribute('data-asset-name')}" akan keluar dari inventaris aktif.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#E11D48',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, nonaktifkan',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>
