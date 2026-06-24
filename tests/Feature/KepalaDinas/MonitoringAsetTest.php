<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\User;

test('kepala dinas can monitor verified asset data with filters', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'MONITOR-DATA-' . uniqid(),
        'nama_bidang' => 'Bidang Monitoring Data',
        'nama_ruangan' => 'Ruang Monitoring Data',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'MONITOR-DATA-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang Monitoring Lain',
        'nama_ruangan' => 'Ruang Monitoring Lain',
    ]);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);
    $inputter = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-MONITOR-DATA',
        'nama_aset' => 'Laptop Monitoring Data',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Monitoring Data',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 8000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $inputter->id,
    ]);
    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-MONITOR-OTHER',
        'jenis_barang' => 'Server',
        'merk_model' => 'Server Monitoring Lain',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $otherBidang->id,
        'ruangan' => 'Ruang Monitoring Lain',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $inputter->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-MONITOR-PENDING',
        'nama_aset' => 'Aset Pending Monitoring',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Monitoring Data',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 1000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $inputter->id,
    ]);

    $response = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.monitoring-aset.data', [
            'bidang_id' => $bidang->id,
            'kategori' => 'Laptop',
            'search' => 'Laptop',
        ]));

    $response->assertOk();
    $response->assertSee('Monitoring Data Aset');
    $response->assertSee('Laptop Monitoring Data');
    $response->assertSee('REG-MONITOR-DATA');
    $response->assertSee('Rp 8.000.000');
    $response->assertDontSee('Server Monitoring Lain');
    $response->assertDontSee('Aset Pending Monitoring');
});

test('kepala dinas can monitor asset conditions and statuses', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'MONITOR-STATUS-' . uniqid(),
        'nama_bidang' => 'Bidang Monitoring Status',
        'nama_ruangan' => 'Ruang Monitoring Status',
    ]);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);
    $inputter = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-MONITOR-RUSAK',
        'nama_aset' => 'Aset Rusak Monitoring',
        'kode_barang' => 'Printer',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Rusak Ringan',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Monitoring Status',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 2000000,
        'kondisi' => 'Rusak Ringan',
        'status' => 'Maintenance',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $inputter->id,
    ]);
    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-MONITOR-DIPINJAM',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Dipinjam Monitoring',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Monitoring Status',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Dipinjam',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $inputter->id,
    ]);

    $conditionResponse = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.monitoring-aset.kondisi', ['kondisi' => 'Rusak Ringan']));

    $conditionResponse->assertOk();
    $conditionResponse->assertSee('Monitoring Kondisi Aset');
    $conditionResponse->assertSee('Aset Rusak Monitoring');
    $conditionResponse->assertDontSee('Aplikasi Dipinjam Monitoring');

    $statusResponse = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.monitoring-aset.status', ['status' => 'Dipinjam']));

    $statusResponse->assertOk();
    $statusResponse->assertSee('Monitoring Status Aset');
    $statusResponse->assertSee('Aplikasi Dipinjam Monitoring');
    $statusResponse->assertDontSee('Aset Rusak Monitoring');
});

test('kepala dinas can open read only monitoring asset detail', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'MONITOR-DETAIL-' . uniqid(),
        'nama_bidang' => 'Bidang Monitoring Detail',
        'nama_ruangan' => 'Ruang Monitoring Detail',
    ]);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);
    $inputter = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-MONITOR-DETAIL',
        'nama_aset' => 'Aset Detail Monitoring',
        'kode_barang' => 'Meja',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Monitoring Detail',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 1500000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $inputter->id,
    ]);

    $response = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.monitoring-aset.show', ['register', $asset->id]));

    $response->assertOk();
    $response->assertSee('Detail Monitoring Aset');
    $response->assertSee('Aset Detail Monitoring');
    $response->assertSee('Kondisi dan Status');
    $response->assertSee('Lihat Detail QR');
    $response->assertDontSee('Simpan Perubahan');
});
