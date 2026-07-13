<?php

namespace App\Support;

use App\Models\Bidang;
use Illuminate\Support\Collection;

class DefaultBidang
{
    /**
     * Daftar bidang default Diskominfotik.
     *
     * @return array<int, array<string, string>>
     */
    public static function data(): array
    {
        return [
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
                'deskripsi' => 'Bagian Sekretariat Diskominfotik Provinsi Riau',
            ],
            [
                'kode_bidang' => 'IKP',
                'nama_bidang' => 'IKP',
                'nama_ruangan' => 'Ruang IKP',
                'deskripsi' => 'Bidang Informasi dan Komunikasi Publik Diskominfotik Provinsi Riau',
            ],
            [
                'kode_bidang' => 'INF',
                'nama_bidang' => 'Infrastruktur',
                'nama_ruangan' => 'Ruang Infrastruktur',
                'deskripsi' => 'Bidang Infrastruktur Teknologi Informasi dan Komunikasi Diskominfotik Provinsi Riau',
            ],
        ];
    }

    /**
     * Pastikan bidang default tersedia dan kembalikan daftar bidang.
     *
     * @return Collection<int, Bidang>
     */
    public static function ensure(): Collection
    {
        foreach (self::data() as $bidang) {
            Bidang::updateOrCreate(
                ['kode_bidang' => $bidang['kode_bidang']],
                $bidang
            );
        }

        return Bidang::orderBy('nama_bidang')->get();
    }
}
