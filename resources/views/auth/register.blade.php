<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- NIP -->
        <div>
            <x-input-label for="nip" :value="__('NIP (Nomor Induk Pegawai)')" />
            <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip')" required autofocus autocomplete="nip" />
            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
        </div>

        <!-- Nama -->
        <div class="mt-4">
            <x-input-label for="nama" :value="__('Nama Lengkap')" />
            <x-text-input id="nama" class="block mt-1 w-full" type="text" name="nama" :value="old('nama')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('nama')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- No HP -->
        <div class="mt-4">
            <x-input-label for="no_hp" :value="__('Nomor HP / WhatsApp')" />
            <x-text-input id="no_hp" class="block mt-1 w-full" type="text" name="no_hp" :value="old('no_hp')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Role')" />
            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required onchange="toggleBidangField()">
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih Role</option>
                <option value="Super Admin" {{ old('role') === 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="Admin Perbidang" {{ old('role') === 'Admin Perbidang' ? 'selected' : '' }}>Admin Perbidang</option>
                <option value="Kepala Dinas" {{ old('role') === 'Kepala Dinas' ? 'selected' : '' }}>Kepala Dinas</option>
                <option value="User" {{ old('role') === 'User' ? 'selected' : '' }}>User</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Bidang -->
        <div class="mt-4" id="bidang_container">
            <x-input-label for="bidang_id" :value="__('Bidang')" />
            <select id="bidang_id" name="bidang_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="" selected>Pilih Bidang (Opsional / Tidak Ada)</option>
                @foreach($bidangs as $bidang)
                    <option value="{{ $bidang->id }}" {{ old('bidang_id') == $bidang->id ? 'selected' : '' }}>
                        {{ $bidang->nama_bidang }} ({{ $bidang->kode_bidang }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('bidang_id')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Sudah terdaftar?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Daftar') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function toggleBidangField() {
            var roleSelect = document.getElementById('role');
            var bidangContainer = document.getElementById('bidang_container');
            
            if (roleSelect.value === 'Admin Perbidang') {
                bidangContainer.style.display = 'block';
            } else {
                bidangContainer.style.display = 'none';
                document.getElementById('bidang_id').value = '';
            }
        }
        // Jalankan pada load pertama kali
        document.addEventListener('DOMContentLoaded', toggleBidangField);
    </script>
</x-guest-layout>
