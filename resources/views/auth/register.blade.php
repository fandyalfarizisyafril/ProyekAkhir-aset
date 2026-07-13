<x-guest-layout>
    <div class="mb-6 text-center">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Registrasi Super Admin</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Buat Akun Pengelola Utama</h1>
        <p class="mt-2 text-sm leading-6 text-slate-500">
            Form ini hanya digunakan untuk membuat akun Super Admin. Akun Admin Perbidang dan Kepala Dinas dibuat melalui menu Manajemen Pengguna.
        </p>
    </div>

    @if ($superAdminExists)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p class="font-semibold">Registrasi publik sudah ditutup.</p>
            <p class="mt-1 leading-6">
                Akun Super Admin sudah tersedia. Silakan login, lalu buat akun Admin Perbidang atau Kepala Dinas dari halaman Manajemen Pengguna.
            </p>
        </div>

        <div class="mt-6">
            <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-md bg-[#0F3092] px-4 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-[#0F3092] focus:ring-offset-2">
                Masuk ke Sistem
            </a>
        </div>
    @else
        @if ($errors->has('register'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                {{ $errors->first('register') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-input-label for="nip" :value="__('NIP (Nomor Induk Pegawai)')" />
                <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('nip')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="nama" :value="__('Nama Lengkap')" />
                <x-text-input id="nama" class="block mt-1 w-full" type="text" name="nama" :value="old('nama')" required autocomplete="name" />
                <x-input-error :messages="$errors->get('nama')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="no_hp" :value="__('Nomor HP / WhatsApp')" />
                <x-text-input id="no_hp" class="block mt-1 w-full" type="text" name="no_hp" :value="old('no_hp')" autocomplete="tel" />
                <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
            </div>

            <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700">Role Akun</p>
                <p class="mt-1 text-sm font-semibold text-slate-900">Super Admin</p>
            </div>

            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="mt-6 flex items-center justify-between gap-4">
                <a class="text-sm font-semibold text-slate-500 transition hover:text-[#0F3092]" href="{{ route('login') }}">
                    Sudah punya akun?
                </a>

                <x-primary-button>
                    Daftar Super Admin
                </x-primary-button>
            </div>
        </form>
    @endif
</x-guest-layout>
