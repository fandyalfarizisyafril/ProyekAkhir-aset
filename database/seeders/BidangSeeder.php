<?php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bidangs = [
            [
                'kode_bidang' => 'PSD',
                'nama_bidang' => 'Persandian',
                'nama_ruangan' => 'Ruang Persandian',
                'deskripsi' => 'Bidang Persandian Diskominfotik Provinsi Riau',
            ],
            [
                'kode_bidang' => 'APT',
                'nama_bidang' => 'Aptika',
                'nama_ruangan' => 'Ruang Aptika',
                'deskripsi' => 'Bidang Aplikasi Informatika (Aptika) Diskominfotik Provinsi Riau',
            ],
            [
                'kode_bidang' => 'STT',
                'nama_bidang' => 'Statistik',
                'nama_ruangan' => 'Ruang Statistik',
                'deskripsi' => 'Bidang Statistik Diskominfotik Provinsi Riau',
            ],
            [
                'kode_bidang' => 'SEK',
                'nama_bidang' => 'Sekretariat',
                'nama_ruangan' => 'Ruang Sekretariat',
                'deskripsi' => 'Bagian Sekretariat (barang-barang umum) Diskominfotik Provinsi Riau',
            ],
            [
                'kode_bidang' => 'IKP',
                'nama_bidang' => 'IKP',
                'nama_ruangan' => 'Ruang IKP',
                'deskripsi' => 'Bidang Informasi dan Komunikasi Publik (IKP) Diskominfotik Provinsi Riau',
            ],
            [
                'kode_bidang' => 'INF',
                'nama_bidang' => 'Infrastruktur',
                'nama_ruangan' => 'Ruang Infrastruktur',
                'deskripsi' => 'Bidang Infrastruktur Teknologi Informasi dan Komunikasi Diskominfotik Provinsi Riau',
            ],
        ];

        foreach ($bidangs as $bidang) {
            Bidang::updateOrCreate(
                ['kode_bidang' => $bidang['kode_bidang']],
                $bidang
            );
        }
    }
}
