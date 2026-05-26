<x-app-layout>
    <!-- Welcome Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            Ringkasan Manajemen Aset
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Selamat datang kembali. Berikut adalah status aset terkini di Diskominfotik Provinsi Riau.
        </p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-dashboard.stats-card 
            title="Total Seluruh Aset" 
            value="2.482" 
            trend="+12 bulan ini" 
            type="info" 
        />
        <x-dashboard.stats-card 
            title="Aset Kondisi Baik" 
            value="2.315" 
            trend="93.2% dari total aset" 
            type="success" 
        />
        <x-dashboard.stats-card 
            title="Aset Rusak / Perbaikan" 
            value="167" 
            trend="24 Butuh Segera" 
            type="danger" 
        />
    </div>

    <!-- Main Content Grid: Filters, Sebaran Aset & Kondisi Fisik -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Left Panel: Filters & Progress Bars (Spans 2 columns) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Filters Card (Admin Perbidang: No Bidang filter, with Ekspor Laporan button) -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <form action="#" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                    <!-- Kategori Dropdown -->
                    <div class="flex-1 w-full">
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KATEGORI
                        </label>
                        <div class="relative">
                            <select class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                                <option>Semua Kategori</option>
                                <option>Elektronik</option>
                                <option>Kendaraan</option>
                                <option>Peralatan Kantor</option>
                                <option>Mebel</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Kondisi Dropdown -->
                    <div class="flex-1 w-full">
                        <label class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                            KONDISI
                        </label>
                        <div class="relative">
                            <select class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl px-4 py-3 appearance-none focus:outline-none focus:border-[#0F3092] transition-colors font-medium">
                                <option>Semua Kondisi</option>
                                <option>Baik</option>
                                <option>Rusak Ringan</option>
                                <option>Rusak Berat</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <!-- Terapkan Button -->
                        <button type="submit" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Terapkan</span>
                        </button>

                        <!-- Ekspor Laporan Button -->
                        <button type="button" class="w-full sm:w-auto bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-6 py-3.5 rounded-xl flex items-center justify-center space-x-2 transition-all duration-150 shadow-sm">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Ekspor Laporan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Sebaran Aset Per Bidang Card -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <!-- Card Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 tracking-tight">
                            Sebaran Aset Per Bidang
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Visualisasi distribusi aset berdasarkan unit kerja fungsional
                        </p>
                    </div>
                    <!-- Year dropdown badge -->
                    <div class="mt-2 sm:mt-0 bg-slate-50 border border-slate-100 text-slate-600 text-[11px] font-semibold px-3 py-1.5 rounded-xl flex items-center space-x-2">
                        <span>Tahun Anggaran 2025</span>
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>

                <!-- Progress Bars List -->
                <div class="space-y-5">
                    <!-- IKP Row -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-1.5">
                            <span class="tracking-wider text-[10px] uppercase text-slate-500">IKP</span>
                            <span>250 Unit</span>
                        </div>
                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: 50%"></div>
                        </div>
                    </div>

                    <!-- INFRASTRUKTUR Row -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-1.5">
                            <span class="tracking-wider text-[10px] uppercase text-slate-500">INFRASTRUKTUR</span>
                            <span>400 Unit</span>
                        </div>
                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-[#0F3092] h-full rounded-full transition-all duration-500" style="width: 80%"></div>
                        </div>
                    </div>

                    <!-- APLIKASI Row -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-1.5">
                            <span class="tracking-wider text-[10px] uppercase text-slate-500">APLIKASI</span>
                            <span>150 Unit</span>
                        </div>
                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full transition-all duration-500" style="width: 30%"></div>
                        </div>
                    </div>

                    <!-- PERSANDIAN Row -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-1.5">
                            <span class="tracking-wider text-[10px] uppercase text-slate-500">PERSANDIAN</span>
                            <span>100 Unit</span>
                        </div>
                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-sky-400 h-full rounded-full transition-all duration-500" style="width: 20%"></div>
                        </div>
                    </div>

                    <!-- SEKRETARIAT Row -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-700 mb-1.5">
                            <span class="tracking-wider text-[10px] uppercase text-slate-500">SEKRETARIAT</span>
                            <span>200 Unit</span>
                        </div>
                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden">
                            <div class="bg-sky-300 h-full rounded-full transition-all duration-500" style="width: 40%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Kondisi Fisik Donut Chart (Spans 1 column) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 h-full flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800 tracking-tight mb-6">
                        Kondisi Fisik
                    </h3>

                    <!-- SVG Donut Chart Container -->
                    <div class="relative flex justify-center items-center my-8">
                        <svg class="w-48 h-48 transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="30" fill="transparent" stroke="#F1F5F9" stroke-width="12"/>
                            <circle cx="50" cy="50" r="30" fill="transparent" stroke="#10B981" stroke-width="12" 
                                    stroke-dasharray="154.5 188.4" stroke-dashoffset="0" stroke-linecap="round"/>
                            <circle cx="50" cy="50" r="30" fill="transparent" stroke="#F59E0B" stroke-width="12" 
                                    stroke-dasharray="22.6 188.4" stroke-dashoffset="-154.5" stroke-linecap="round"/>
                            <circle cx="50" cy="50" r="30" fill="transparent" stroke="#EF4444" stroke-width="12" 
                                    stroke-dasharray="11.3 188.4" stroke-dashoffset="-177.1" stroke-linecap="round"/>
                        </svg>

                        <div class="absolute flex flex-col items-center justify-center text-center">
                            <span class="text-3xl font-extrabold text-slate-800 tracking-tight leading-none">1.7K</span>
                            <span class="text-[9px] font-bold text-slate-400 tracking-widest uppercase mt-1">TOTAL UNIT</span>
                        </div>
                    </div>
                </div>

                <!-- Legend / Percentage List -->
                <div class="border-t border-slate-100 pt-6 space-y-4">
                    <!-- Baik row -->
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3 text-xs font-semibold text-slate-600">
                            <span class="h-3 w-3 bg-[#10B981] rounded-full inline-block"></span>
                            <span>Baik</span>
                        </div>
                        <span class="text-xs font-bold text-slate-800">82%</span>
                    </div>

                    <!-- Rusak Ringan row -->
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3 text-xs font-semibold text-slate-600">
                            <span class="h-3 w-3 bg-[#F59E0B] rounded-full inline-block"></span>
                            <span>Rusak Ringan</span>
                        </div>
                        <span class="text-xs font-bold text-slate-800">12%</span>
                    </div>

                    <!-- Rusak Berat row -->
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3 text-xs font-semibold text-slate-600">
                            <span class="h-3 w-3 bg-[#EF4444] rounded-full inline-block"></span>
                            <span>Rusak Berat</span>
                        </div>
                        <span class="text-xs font-bold text-slate-800">6%</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
