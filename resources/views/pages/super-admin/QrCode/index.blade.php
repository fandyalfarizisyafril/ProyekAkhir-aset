<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                Registrasi QR Code Aset
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Buat, cetak, dan unduh label QR untuk aset yang sudah terverifikasi.
            </p>
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-dashboard.stats-card
            title="Aset Terverifikasi"
            value="{{ number_format($eligibleCount) }}"
            trend="Layak dibuat QR"
            type="info"
        />
        <x-dashboard.stats-card
            title="Sudah QR"
            value="{{ number_format($generatedCount) }}"
            trend="Label siap cetak"
            type="success"
        />
        <x-dashboard.stats-card
            title="Belum QR"
            value="{{ number_format($missingCount) }}"
            trend="Perlu generate"
            type="danger"
        />
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8 space-y-6">
        <form action="{{ route('super-admin.qr-code.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <select name="jenis" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Jenis" {{ $filters['jenis'] === 'Semua Jenis' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="register" {{ $filters['jenis'] === 'register' ? 'selected' : '' }}>Register</option>
                    <option value="smki" {{ $filters['jenis'] === 'smki' ? 'selected' : '' }}>SMKI</option>
                </select>

                <select name="status_qr" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua QR" {{ $filters['status_qr'] === 'Semua QR' ? 'selected' : '' }}>Semua QR</option>
                    <option value="Sudah QR" {{ $filters['status_qr'] === 'Sudah QR' ? 'selected' : '' }}>Sudah QR</option>
                    <option value="Belum QR" {{ $filters['status_qr'] === 'Belum QR' ? 'selected' : '' }}>Belum QR</option>
                </select>

                <select name="bidang_id" onchange="this.form.submit()" class="w-full bg-white border border-slate-200 text-slate-600 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                    <option value="Semua Bidang" {{ $filters['bidang_id'] === 'Semua Bidang' ? 'selected' : '' }}>Semua Bidang</option>
                    @foreach($bidangs as $bidang)
                        <option value="{{ $bidang->id }}" {{ (string) $filters['bidang_id'] === (string) $bidang->id ? 'selected' : '' }}>
                            {{ $bidang->nama_bidang }}
                        </option>
                    @endforeach
                </select>

                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="Cari aset atau kode..."
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

            @if($filters['search'] || $filters['jenis'] !== 'Semua Jenis' || $filters['status_qr'] !== 'Semua QR' || $filters['bidang_id'] !== 'Semua Bidang')
                <a href="{{ route('super-admin.qr-code.index') }}" class="inline-block text-[#0F3092] hover:text-[#0B2F83] text-xs font-semibold hover:underline">
                    Reset Filter
                </a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-4">Aset</th>
                        <th class="py-4 px-4">Jenis</th>
                        <th class="py-4 px-4">Bidang</th>
                        <th class="py-4 px-4">Kondisi</th>
                        <th class="py-4 px-4">Status QR</th>
                        <th class="py-4 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800 text-sm">{{ $asset->name }}</div>
                                <div class="text-[10px] text-slate-400 mt-1">
                                    <span class="font-semibold text-slate-600">{{ $asset->code }}</span>
                                    <span class="px-1">|</span>
                                    <span>{{ $asset->category }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 text-[9px] font-extrabold tracking-wider rounded-md bg-[#EBF3FF] text-[#0F3092] border border-[#CBD5E1]">
                                    {{ $asset->type_label }}
                                </span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-500">
                                {{ $asset->bidang->nama_bidang ?? '-' }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-slate-600">
                                {{ $asset->condition }}
                            </td>
                            <td class="py-4 px-4">
                                @if($asset->qr_code_path)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Sudah QR
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold leading-5 bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum QR
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if(! $asset->qr_code_path)
                                        <form action="{{ route('super-admin.qr-code.generate', [$asset->type, $asset->id]) }}" method="POST" class="inline generate-form" data-asset-name="{{ $asset->name }}">
                                            @csrf
                                            <button type="submit" class="text-[#0F3092] hover:text-blue-800 transition-colors p-1 hover:bg-blue-50 rounded" title="Generate QR">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 4h4v4H4V4zm0 12h4v4H4v-4zm12 0h4v4h-4v-4zM4 8h.01M4 12h.01M12 12h.01M16 8h.01M8 10h.01M12 16h.01" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('super-admin.qr-code.label', [$asset->type, $asset->id]) }}" class="text-slate-500 hover:text-slate-700 transition-colors p-1 hover:bg-slate-100 rounded" title="Cetak Label">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v4H6v-4z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('super-admin.qr-code.download', [$asset->type, $asset->id]) }}" class="text-emerald-600 hover:text-emerald-700 transition-colors p-1 hover:bg-emerald-50 rounded" title="Download QR">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-slate-400 font-medium bg-slate-50/50">
                                Tidak ada aset terverifikasi yang cocok dengan filter QR.
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.generate-form').forEach(form => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const assetName = this.getAttribute('data-asset-name');

                    Swal.fire({
                        title: 'Generate QR Code',
                        text: `Buat QR Code untuk aset "${assetName}"?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#002D84',
                        cancelButtonColor: '#64748B',
                        confirmButtonText: 'Ya, buat QR',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            @if(session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonColor: '#002D84',
                    confirmButtonText: 'OK'
                });
            @endif
        });
    </script>
</x-app-layout>
