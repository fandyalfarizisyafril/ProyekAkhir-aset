<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Super Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Selamat Datang, {{ Auth::user()->nama }}!</h3>
                    <p class="text-gray-600 mb-2">Anda login sebagai <strong>Super Admin</strong>. Anda memiliki hak akses penuh terhadap seluruh konfigurasi sistem, data bidang, manajemen aset, transaksi, dan data pengguna.</p>
                    <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded text-blue-700">
                        <strong>NIP:</strong> {{ Auth::user()->nip }}<br>
                        <strong>Email:</strong> {{ Auth::user()->email }}<br>
                        <strong>No HP:</strong> {{ Auth::user()->no_hp ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
