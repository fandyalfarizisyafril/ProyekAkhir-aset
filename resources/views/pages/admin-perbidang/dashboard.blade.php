<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin Perbidang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Selamat Datang, {{ Auth::user()->nama }}!</h3>
                    <p class="text-gray-600 mb-2">Anda login sebagai <strong>Admin Perbidang</strong>. Anda memiliki akses untuk mengelola pendaftaran aset baru, riwayat kondisi aset, pengajuan mutasi, serta peminjaman aset di lingkup bidang Anda.</p>
                    <div class="mt-4 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded text-emerald-700">
                        <strong>NIP:</strong> {{ Auth::user()->nip }}<br>
                        <strong>Email:</strong> {{ Auth::user()->email }}<br>
                        <strong>Bidang Tugas:</strong> {{ Auth::user()->bidang ? Auth::user()->bidang->nama_bidang . ' (' . Auth::user()->bidang->kode_bidang . ')' : 'Tidak Terikat Bidang' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
