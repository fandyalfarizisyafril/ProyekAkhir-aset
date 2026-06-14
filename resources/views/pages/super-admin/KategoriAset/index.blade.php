<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Kategori Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Kelola kategori aset Register dan SMKI agar input aset lebih konsisten.
            </p>
        </div>

        <a href="{{ route('super-admin.kategori-aset.create') }}" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Kategori</span>
        </a>
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
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-dashboard.stats-card title="Total Kategori" value="{{ number_format($totalCount) }}" trend="Register dan SMKI" type="info" />
        <x-dashboard.stats-card title="Register" value="{{ number_format($registerCount) }}" trend="Kategori aset fisik" type="success" />
        <x-dashboard.stats-card title="SMKI" value="{{ number_format($smkiCount) }}" trend="Kategori aset informasi" type="success" />
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <form action="{{ route('super-admin.kategori-aset.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <select name="tipe" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                <option value="Semua Tipe" {{ $filters['tipe'] === 'Semua Tipe' ? 'selected' : '' }}>Semua Tipe</option>
                <option value="Register" {{ $filters['tipe'] === 'Register' ? 'selected' : '' }}>Register</option>
                <option value="SMKI" {{ $filters['tipe'] === 'SMKI' ? 'selected' : '' }}>SMKI</option>
            </select>

            <select name="bidang_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                @foreach($bidangs as $bidang)
                    <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>{{ $bidang->nama_bidang }}</option>
                @endforeach
            </select>

            <div class="relative md:col-span-2">
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Cari nama kategori, deskripsi, atau bidang..."
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
        </form>

        @if($filters['search'] || $filters['tipe'] !== 'Semua Tipe' || $filters['bidang_id'] !== 'Semua Bidang')
            <a href="{{ route('super-admin.kategori-aset.index') }}" class="inline-block text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold hover:underline">
                Reset Filter
            </a>
        @endif

        <div class="responsive-table">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Nama Kategori</th>
                        <th class="py-4 px-4">Tipe</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Deskripsi</th>
                        <th class="py-4 px-4">Dibuat</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $category->nama_kategori }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $category->tipe === 'Register' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                    {{ $category->tipe }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ $category->bidang->nama_bidang ?? 'Super Admin' }}
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-500">
                                {{ $category->deskripsi ?: '-' }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ optional($category->created_at)->format('d M Y') }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="{{ route('super-admin.kategori-aset.edit', $category->id) }}" class="text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Edit Kategori">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('super-admin.kategori-aset.destroy', $category->id) }}" method="POST" class="inline delete-form" data-category-name="{{ $category->nama_kategori }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 transition-colors p-1 hover:bg-rose-50 rounded" title="Hapus Kategori">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0H7m3-3h4a1 1 0 011 1v2H9V5a1 1 0 011-1z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Belum ada kategori aset.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row justify-between items-center text-xs font-semibold text-slate-500 gap-4">
                <div>
                    Menampilkan {{ $categories->firstItem() ?? 0 }}-{{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} kategori
                </div>
                <div>
                    {{ $categories->links() }}
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const categoryName = this.getAttribute('data-category-name');

                    Swal.fire({
                        title: 'Hapus Kategori?',
                        text: `Kategori "${categoryName}" akan dihapus jika belum digunakan aset.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DC2626',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, hapus',
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
