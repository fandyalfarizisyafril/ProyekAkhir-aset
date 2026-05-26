<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
            Tambah Pengguna Baru
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Silakan masukkan detail pegawai dan hak akses sistem untuk mendaftarkan akun baru.
        </p>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-4xl">
        <form action="{{ route('super-admin.pengguna.store') }}" method="POST" class="space-y-6" id="create-user-form">
            @csrf

            <!-- Form Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- NIP Field -->
                <div>
                    <label for="nip" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        NIP / Nomor Induk Pegawai
                    </label>
                    <input 
                        type="text" 
                        id="nip" 
                        name="nip" 
                        value="{{ old('nip') }}"
                        placeholder="Masukkan NIP pegawai"
                        class="w-full bg-slate-50 border @error('nip') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('nip')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Pegawai Field -->
                <div>
                    <label for="nama" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Nama Lengkap Pegawai
                    </label>
                    <input 
                        type="text" 
                        id="nama" 
                        name="nama" 
                        value="{{ old('nama') }}"
                        placeholder="Masukkan nama lengkap pegawai"
                        class="w-full bg-slate-50 border @error('nama') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('nama')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Alamat Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        placeholder="contoh@riau.go.id"
                        class="w-full bg-slate-50 border @error('email') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('email')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor HP Field -->
                <div>
                    <label for="no_hp" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Nomor HP / WhatsApp
                    </label>
                    <input 
                        type="text" 
                        id="no_hp" 
                        name="no_hp" 
                        value="{{ old('no_hp') }}"
                        placeholder="Contoh: 081234567890"
                        class="w-full bg-slate-50 border @error('no_hp') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('no_hp')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Kata Sandi (Password)
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Minimal 8 karakter"
                        class="w-full bg-slate-50 border @error('password') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 focus:outline-none transition-colors font-medium"
                    >
                    @error('password')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role / Peran Field -->
                <div>
                    <label for="role" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Peran Akses (Role)
                    </label>
                    <div class="relative">
                        <select 
                            id="role" 
                            name="role" 
                            class="w-full bg-slate-50 border @error('role') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                        >
                            <option value="" disabled selected>Pilih peran pengguna</option>
                            @foreach($roles as $roleOption)
                                <option value="{{ $roleOption }}" {{ old('role') === $roleOption ? 'selected' : '' }}>
                                    {{ $roleOption }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('role')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bidang / Unit Kerja Field -->
                <div>
                    <label for="bidang_id" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Bidang / Unit Kerja
                    </label>
                    <div class="relative">
                        <select 
                            id="bidang_id" 
                            name="bidang_id" 
                            class="w-full bg-slate-50 border @error('bidang_id') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                        >
                            <option value="">Tidak Terikat Bidang</option>
                            @foreach($bidangs as $bidang)
                                <option value="{{ $bidang->id }}" {{ old('bidang_id') == $bidang->id ? 'selected' : '' }}>
                                    {{ $bidang->nama_bidang }} ({{ $bidang->kode_bidang }})
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('bidang_id')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Field -->
                <div>
                    <label for="status" class="block text-[10px] font-bold text-slate-400 tracking-wider uppercase mb-2">
                        Status Akun
                    </label>
                    <div class="relative">
                        <select 
                            id="status" 
                            name="status" 
                            class="w-full bg-slate-50 border @error('status') border-red-300 focus:border-red-500 @else border-slate-200 focus:border-[#0F3092] @enderror text-slate-700 text-xs rounded-xl px-4 py-3.5 appearance-none focus:outline-none transition-colors font-medium"
                        >
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}" {{ (old('status', 'Aktif') === $statusOption) ? 'selected' : '' }}>
                                    {{ $statusOption }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    @error('status')
                        <p class="text-red-500 text-[10px] font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4 border-t border-slate-100 pt-6">
                <!-- Batal Button -->
                <a href="{{ route('super-admin.pengguna.index') }}" class="px-5 py-3 border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-500 uppercase tracking-wider transition-colors">
                    Batal
                </a>
                
                <!-- Simpan Button -->
                <button type="submit" class="bg-[#002D84] hover:bg-[#0B2F83] text-white text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition-all duration-150 shadow-sm flex items-center justify-center">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>

    <!-- SweetAlert2 Form Confirmation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('create-user-form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (this.getAttribute('data-confirmed') === 'true') {
                        return;
                    }
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Simpan',
                        text: 'Apakah Anda yakin data yang dimasukkan sudah benar?',
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
