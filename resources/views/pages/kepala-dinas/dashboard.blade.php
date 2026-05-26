<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Kepala Dinas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Selamat Datang, Bapak/Ibu Kepala Dinas!</h3>
                    <p class="text-gray-600 mb-2">Anda login sebagai <strong>Kepala Dinas</strong>. Halaman ini menyediakan akses untuk meninjau laporan mutasi aset, menyetujui peminjaman, serta melihat riwayat penyusutan dan verifikasi aset dinas secara komprehensif.</p>
                    <div class="mt-4 p-4 bg-purple-50 border-l-4 border-purple-500 rounded text-purple-700">
                        <strong>Nama Lengkap:</strong> {{ Auth::user()->nama }}<br>
                        <strong>NIP:</strong> {{ Auth::user()->nip }}<br>
                        <strong>Email:</strong> {{ Auth::user()->email }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
