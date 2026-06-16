<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\KategoriAset;
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
    expect(KategoriAset::where('tipe', 'Register')->where('nama_kategori', 'KB-REG-STATUS')->exists())->toBeFalse();

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

test('admin perbidang cannot delete register asset from list or endpoint', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-NO-DELETE-' . uniqid(),
        'nama_bidang' => 'Bidang Register Tanpa Hapus',
        'nama_ruangan' => 'Ruang Register Tanpa Hapus',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-NO-DELETE-001',
        'nama_aset' => 'Aset Register Tidak Bisa Dihapus Admin',
        'kode_barang' => 'KB-NO-DELETE',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Tanpa Hapus',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $indexResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Aset Register Tidak Bisa Dihapus Admin');
    $indexResponse->assertDontSee('delete-form');
    $indexResponse->assertDontSee('Hapus Aset');

    $deleteResponse = $this->actingAs($admin)
        ->delete('/admin-perbidang/data-aset-register/' . $asset->id);

    $deleteResponse->assertMethodNotAllowed();
    expect(AsetRegister::whereKey($asset->id)->exists())->toBeTrue();
});

test('admin perbidang cannot delete smki asset from list or endpoint', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'SMKI-NO-DELETE-' . uniqid(),
        'nama_bidang' => 'Bidang SMKI Tanpa Hapus',
        'nama_ruangan' => 'Ruang SMKI Tanpa Hapus',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-NO-DELETE-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aset SMKI Tidak Bisa Dihapus Admin',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang SMKI Tanpa Hapus',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $indexResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-smki.index'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Aset SMKI Tidak Bisa Dihapus Admin');
    $indexResponse->assertDontSee('delete-form');
    $indexResponse->assertDontSee('Hapus Aset');

    $deleteResponse = $this->actingAs($admin)
        ->delete('/admin-perbidang/data-aset-smki/' . $asset->id);

    $deleteResponse->assertMethodNotAllowed();
    expect(AsetSmki::whereKey($asset->id)->exists())->toBeTrue();
});
