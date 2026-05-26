<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Selamat Datang, {{ Auth::user()->nama }}!</h3>
                    <p class="text-gray-600 mb-2">Anda terdaftar sebagai <strong>User</strong> umum di sistem PA-aset. Di sini Anda dapat memantau status aset dan riwayat peminjaman pribadi.</p>
                    <div class="mt-4 p-4 bg-gray-50 border-l-4 border-gray-400 rounded text-gray-700">
                        <strong>NIP:</strong> {{ Auth::user()->nip }}<br>
                        <strong>Email:</strong> {{ Auth::user()->email }}<br>
                        <strong>No HP:</strong> {{ Auth::user()->no_hp ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
