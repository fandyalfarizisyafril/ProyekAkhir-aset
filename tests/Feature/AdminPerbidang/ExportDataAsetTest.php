<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\User;

test('admin perbidang can export filtered register assets from own bidang', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'EXPORT-REG-' . uniqid(),
        'nama_bidang' => 'Bidang Export Register',
        'nama_ruangan' => 'Ruang Export Register',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'EXPORT-REG-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang Export Register Lain',
        'nama_ruangan' => 'Ruang Export Register Lain',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-EXPORT-001',
        'nama_aset' => 'Laptop Export Register',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Export Register',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 6500000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-EXPORT-002',
        'nama_aset' => 'Printer Tidak Ikut Filter',
        'kode_barang' => 'Printer',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Export Register',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 2000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-EXPORT-DELETED',
        'nama_aset' => 'Laptop Dihapus Tidak Diexport',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '003',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Export Register',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 1000000,
        'kondisi' => 'Baik',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-EXPORT-OTHER',
        'nama_aset' => 'Laptop Bidang Lain Tidak Diexport',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '004',
        'bidang_id' => $otherBidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang Lain',
        'lokasi_aset' => 'Ruang Lain',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 3000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.export', [
            'kategori' => 'Laptop',
            'status' => 'Terverifikasi',
            'search' => 'Laptop',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('data-aset-register-');
    expect($response->headers->get('content-disposition'))->toContain('.xls');

    $excel = $response->streamedContent();
    expect($excel)->toContain('<table border="1">');
    expect($excel)->toContain('Kode Aset');
    expect($excel)->toContain('REG-EXPORT-001');
    expect($excel)->toContain('Laptop Export Register');
    expect($excel)->toContain('6500000');
    expect($excel)->not->toContain('Printer Tidak Ikut Filter');
    expect($excel)->not->toContain('Laptop Dihapus Tidak Diexport');
    expect($excel)->not->toContain('Laptop Bidang Lain Tidak Diexport');
});

test('admin perbidang can export filtered smki assets from own bidang', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'EXPORT-SMKI-' . uniqid(),
        'nama_bidang' => 'Bidang Export SMKI',
        'nama_ruangan' => 'Ruang Export SMKI',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-EXPORT-001',
        'jenis_barang' => 'Server',
        'merk_model' => 'Server Export SMKI',
        'no_ser_model' => 'SN-EXPORT-001',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Export SMKI',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-EXPORT-002',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Tidak Ikut Filter',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Export SMKI',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-EXPORT-DELETED',
        'jenis_barang' => 'Server',
        'merk_model' => 'Server Dihapus Tidak Diexport',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Export SMKI',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-smki.export', [
            'kategori' => 'Server',
            'status' => 'Terverifikasi',
            'search' => 'Server',
        ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('data-aset-smki-');
    expect($response->headers->get('content-disposition'))->toContain('.xls');

    $excel = $response->streamedContent();
    expect($excel)->toContain('<table border="1">');
    expect($excel)->toContain('Nomor Kode Barang');
    expect($excel)->toContain('SMKI-EXPORT-001');
    expect($excel)->toContain('Server Export SMKI');
    expect($excel)->not->toContain('Aplikasi Tidak Ikut Filter');
    expect($excel)->not->toContain('Server Dihapus Tidak Diexport');
});
