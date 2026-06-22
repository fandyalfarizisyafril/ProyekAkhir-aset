<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PenghapusanAset;
use App\Models\User;

test('admin perbidang can view deleted assets from own bidang in asset history', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'HISTORY-OWN-' . uniqid(),
        'nama_bidang' => 'Bidang Riwayat Sendiri',
        'nama_ruangan' => 'Ruang Riwayat Sendiri',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'HISTORY-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang Riwayat Lain',
        'nama_ruangan' => 'Ruang Riwayat Lain',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $otherAdmin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $otherBidang->id,
    ]);
    $superAdmin = User::factory()->create([
        'role' => 'Super Admin',
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-HISTORY-ACTIVE',
        'nama_aset' => 'Aset Register Masih Aktif',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Riwayat Sendiri',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $deletedRegister = AsetRegister::create([
        'kode_aset' => 'REG-HISTORY-DELETED',
        'nama_aset' => 'Aset Register Nonaktif Bidang',
        'kode_barang' => 'Printer',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Rusak Berat',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Riwayat Sendiri',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 5000000,
        'kondisi' => 'Rusak Berat',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    PenghapusanAset::create([
        'aset_register_id' => $deletedRegister->id,
        'jenis_aset' => 'register',
        'kode_aset' => $deletedRegister->kode_aset,
        'nama_aset' => $deletedRegister->nama_aset,
        'bidang_id' => $bidang->id,
        'nilai_buku' => 2500000,
        'tanggal_penghapusan' => '2026-06-22',
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Sudah rusak berat dan tidak ekonomis diperbaiki.',
        'status_sebelum' => 'Rusak',
        'dihapus_oleh' => $superAdmin->id,
    ]);

    $deletedSmki = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-HISTORY-DELETED',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aset SMKI Nonaktif Bidang',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Riwayat Sendiri',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    PenghapusanAset::create([
        'aset_smki_id' => $deletedSmki->id,
        'jenis_aset' => 'smki',
        'kode_aset' => $deletedSmki->nomor_kode_barang,
        'nama_aset' => $deletedSmki->merk_model,
        'bidang_id' => $bidang->id,
        'nilai_buku' => null,
        'tanggal_penghapusan' => '2026-06-21',
        'metode_penghapusan' => 'Pengalihan',
        'alasan' => 'Diganti oleh sistem baru.',
        'status_sebelum' => 'Tersedia',
        'dihapus_oleh' => $superAdmin->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-HISTORY-OTHER',
        'nama_aset' => 'Aset Nonaktif Bidang Lain',
        'kode_barang' => 'Router',
        'kode_urut_barang' => '003',
        'bidang_id' => $otherBidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang Lain',
        'lokasi_aset' => 'Ruang Riwayat Lain',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 7000000,
        'kondisi' => 'Baik',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $otherAdmin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset.riwayat'));

    $response->assertOk();
    $response->assertSee('Riwayat Aset');
    $response->assertSee('Aset Register Nonaktif Bidang');
    $response->assertSee('Aset SMKI Nonaktif Bidang');
    $response->assertSee('Pemusnahan');
    $response->assertSee('Pengalihan');
    $response->assertSee('Sudah rusak berat dan tidak ekonomis diperbaiki.');
    $response->assertSee('Rp 2.500.000');
    $response->assertSee($superAdmin->name);
    $response->assertDontSee('Aset Register Masih Aktif');
    $response->assertDontSee('Aset Nonaktif Bidang Lain');
});

test('admin perbidang asset history can be filtered by asset type', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'HISTORY-FILTER-' . uniqid(),
        'nama_bidang' => 'Bidang Filter Riwayat',
        'nama_ruangan' => 'Ruang Filter Riwayat',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-HISTORY-FILTER',
        'nama_aset' => 'Register Riwayat Filter',
        'kode_barang' => 'Meja',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Filter Riwayat',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 3000000,
        'kondisi' => 'Baik',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-HISTORY-FILTER',
        'jenis_barang' => 'Server',
        'merk_model' => 'SMKI Riwayat Filter',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Filter Riwayat',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset.riwayat', ['jenis' => 'register']));

    $response->assertOk();
    $response->assertSee('Register Riwayat Filter');
    $response->assertDontSee('SMKI Riwayat Filter');
});
