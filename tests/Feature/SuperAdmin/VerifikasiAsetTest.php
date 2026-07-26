<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\User;

test('super admin can view asset verification queue', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-001',
        'nama_aset' => 'Laptop Inventaris',
        'kode_barang' => 'KB-001',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang TIK',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 12000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $adminBidang->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.verifikasi-aset.index'));

    $response->assertOk();
    $response->assertSee('Laptop Inventaris');
    $response->assertSee('Perlu Verifikasi');
});

test('super admin can approve register asset', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-002',
        'nama_aset' => 'Router Kantor',
        'kode_barang' => 'KB-002',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Server',
        'kerahasiaan' => 'Terbatas',
        'kritikalitas' => 'TINGGI',
        'nilai' => 5000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $adminBidang->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-aset.approve', ['register', $asset->id]));

    $response->assertRedirect(route('super-admin.verifikasi-aset.index'));
    expect($asset->fresh()->status_verifikasi)->toBe('Terverifikasi');
    expect($asset->fresh()->status)->toBe('Tersedia');
    expect($asset->fresh()->diverifikasi_oleh)->toBe($superAdmin->id);
});

test('super admin rejects register asset and marks it as rejected operationally', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-REJECT-' . uniqid(),
        'nama_bidang' => 'Bidang Reject Register',
        'nama_ruangan' => 'Ruang Reject Register',
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-REJECT-001',
        'nama_aset' => 'Meja Ditolak',
        'kode_barang' => 'Furniture',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Reject Register',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 1000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $adminBidang->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-aset.reject', ['register', $asset->id]));

    $response->assertRedirect(route('super-admin.verifikasi-aset.index'));
    expect($asset->fresh()->status_verifikasi)->toBe('Ditolak');
    expect($asset->fresh()->status)->toBe('Ditolak');
    expect($asset->fresh()->diverifikasi_oleh)->toBe($superAdmin->id);
});

test('super admin can reject smki asset', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);

    $asset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Sistem Monitoring',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang TIK',
        'penanggung_jawab' => 'Admin Bidang',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $adminBidang->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-aset.reject', ['smki', $asset->id]));

    $response->assertRedirect(route('super-admin.verifikasi-aset.index'));
    expect($asset->fresh()->status_verifikasi)->toBe('Ditolak');
    expect($asset->fresh()->status)->toBe('Ditolak');
    expect($asset->fresh()->diverifikasi_oleh)->toBe($superAdmin->id);
});
