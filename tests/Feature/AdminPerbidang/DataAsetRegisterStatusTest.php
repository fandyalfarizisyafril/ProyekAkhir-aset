<?php

use App\Models\AsetRegister;
use App\Models\Bidang;
use App\Models\User;

test('new register asset is shown as pending verification on admin perbidang list', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-STATUS-' . uniqid(),
        'nama_bidang' => 'Bidang Register Status',
        'nama_ruangan' => 'Ruang Register Status',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.data-aset-register.store'), [
            'kode_aset' => 'REG-STATUS-001',
            'nama_aset' => 'Laptop Status Verifikasi',
            'kode_barang' => 'KB-REG-STATUS',
            'kode_urut_barang' => '001',
            'status_barang' => 'Baik',
            'pemilik_aset' => 'Diskominfotik Riau',
            'pengguna' => 'Admin Bidang',
            'lokasi_aset' => 'Ruang Register Status',
            'metode_pemusnahan' => null,
            'kerahasiaan' => 'Umum',
            'kritikalitas' => 'SEDANG',
            'nilai' => 10000000,
            'keterangan' => 'Aset register baru untuk verifikasi.',
        ]);

    $response->assertRedirect(route('admin-perbidang.data-aset-register.index'));

    $asset = AsetRegister::first();
    expect($asset->status)->toBe('Aktif');
    expect($asset->status_verifikasi)->toBe('Perlu Verifikasi');

    $indexResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Laptop Status Verifikasi');
    $indexResponse->assertSee('Perlu Verifikasi');
    $indexResponse->assertDontSee('MAINTENANCE');
});

test('register asset status filter uses verification status', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-FILTER-' . uniqid(),
        'nama_bidang' => 'Bidang Register Filter',
        'nama_ruangan' => 'Ruang Register Filter',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-FILTER-PENDING',
        'nama_aset' => 'Aset Register Pending',
        'kode_barang' => 'KB-PENDING',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Filter',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-FILTER-VERIFIED',
        'nama_aset' => 'Aset Register Terverifikasi',
        'kode_barang' => 'KB-VERIFIED',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Filter',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index', ['status' => 'Perlu Verifikasi']));

    $response->assertOk();
    $response->assertSee('Aset Register Pending');
    $response->assertDontSee('Aset Register Terverifikasi');
});
