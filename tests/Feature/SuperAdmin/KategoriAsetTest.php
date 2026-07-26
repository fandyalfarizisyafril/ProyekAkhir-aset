<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\KategoriAset;
use App\Models\User;

test('super admin can manage asset categories', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.kategori-aset.store'), [
            'nama_kategori' => 'Laptop',
            'tipe' => 'Register',
            'deskripsi' => 'Kategori aset fisik laptop.',
        ]);

    $response->assertRedirect(route('super-admin.kategori-aset.index'));

    $category = KategoriAset::first();
    expect($category->nama_kategori)->toBe('Laptop');
    expect($category->tipe)->toBe('Register');

    $indexResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.kategori-aset.index'));

    $indexResponse->assertOk();
    $indexResponse->assertDontSee('Laptop');

    $updateResponse = $this->actingAs($superAdmin)
        ->put(route('super-admin.kategori-aset.update', $category->id), [
            'nama_kategori' => 'Laptop Operasional',
            'tipe' => 'Register',
            'deskripsi' => 'Kategori laptop yang digunakan operasional.',
        ]);

    $updateResponse->assertRedirect(route('super-admin.kategori-aset.index'));
    expect($category->fresh()->nama_kategori)->toBe('Laptop Operasional');

    $deleteResponse = $this->actingAs($superAdmin)
        ->delete(route('super-admin.kategori-aset.destroy', $category->id));

    $deleteResponse->assertRedirect(route('super-admin.kategori-aset.index'));
    expect(KategoriAset::count())->toBe(0);
});

test('super admin cannot create duplicate category for the same type', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    KategoriAset::create([
        'nama_kategori' => 'Aplikasi',
        'tipe' => 'SMKI',
    ]);

    $response = $this->actingAs($superAdmin)
        ->from(route('super-admin.kategori-aset.create'))
        ->post(route('super-admin.kategori-aset.store'), [
            'nama_kategori' => 'Aplikasi',
            'tipe' => 'SMKI',
        ]);

    $response->assertRedirect(route('super-admin.kategori-aset.create'));
    $response->assertSessionHasErrors('nama_kategori');
    expect(KategoriAset::count())->toBe(1);
});

test('super admin cannot delete category that is used by asset', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $admin = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'KAT-' . uniqid(),
        'nama_bidang' => 'Bidang Kategori',
        'nama_ruangan' => 'Ruang Kategori',
    ]);
    $category = KategoriAset::create([
        'nama_kategori' => 'Printer',
        'tipe' => 'Register',
    ]);

    AsetRegister::create([
        'kode_aset' => 'KAT-REG-001',
        'nama_aset' => 'Printer Kategori',
        'kode_barang' => 'Printer',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Kategori',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 1000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->delete(route('super-admin.kategori-aset.destroy', $category->id));

    $response->assertRedirect(route('super-admin.kategori-aset.index'));
    $response->assertSessionHas('error');
    expect(KategoriAset::count())->toBe(1);
});

test('data aset page lists verified asset rows without merging same category', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $admin = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'DATA-ASET-' . uniqid(),
        'nama_bidang' => 'Bidang Data Aset',
        'nama_ruangan' => 'Ruang Data Aset',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'DATA-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang Lain Data Aset',
        'nama_ruangan' => 'Ruang Lain Data Aset',
    ]);

    $firstRegisterAsset = AsetRegister::create([
        'kode_aset' => 'DATA-REG-001',
        'nama_aset' => 'Meja Kerja Kayu',
        'kode_barang' => 'Furniture',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Data Aset',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 1000000,
        'keterangan' => 'Meja operasional bidang.',
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $secondRegisterAsset = AsetRegister::create([
        'kode_aset' => 'DATA-REG-002',
        'nama_aset' => 'Kursi Kerja Ergonomis',
        'kode_barang' => 'Furniture',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Data Aset',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 750000,
        'keterangan' => 'Kursi operasional bidang.',
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $smkiAsset = AsetSmki::create([
        'nomor_kode_barang' => 'DATA-SMKI-001',
        'jenis_barang' => 'Perangkat Keamanan',
        'merk_model' => 'Firewall Appliance',
        'no_ser_model' => 'SN-LEGACY',
        'ukuran' => '2U',
        'bahan' => 'Besi / Baja',
        'tahun_pembuatan' => 2024,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'keterangan' => 'Perangkat pengamanan jaringan.',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Data Aset',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'DATA-REG-PENDING',
        'nama_aset' => 'Aset Pending Register',
        'kode_barang' => 'Furniture',
        'kode_urut_barang' => '003',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Data Aset',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 500000,
        'keterangan' => 'Deskripsi pending tidak boleh masuk.',
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'DATA-REG-OTHER',
        'nama_aset' => 'Aset Bidang Lain',
        'kode_barang' => 'Furniture',
        'kode_urut_barang' => '004',
        'bidang_id' => $otherBidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Lain Data Aset',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 750000,
        'keterangan' => 'Deskripsi bidang lain.',
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.kategori-aset.index'));

    $response->assertOk();
    $response->assertSee('Meja Kerja Kayu');
    $response->assertSee('Kursi Kerja Ergonomis');
    $response->assertSee('Furniture');
    $response->assertSee('Firewall Appliance');
    $response->assertSee('Perangkat Keamanan');
    $response->assertSee('Bidang Data Aset');
    $response->assertDontSee('Aset Pending Register');
    $response->assertDontSee('Edit Kategori');
    $response->assertDontSee('Hapus Kategori');
    $response->assertSee(route('super-admin.verifikasi-aset.show', ['register', $firstRegisterAsset->id]), false);
    $response->assertSee(route('super-admin.verifikasi-aset.show', ['register', $secondRegisterAsset->id]), false);
    $response->assertSee(route('super-admin.verifikasi-aset.show', ['smki', $smkiAsset->id]), false);

    $filteredResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.kategori-aset.index', ['bidang_id' => $bidang->id]));

    $filteredResponse->assertOk();
    $filteredResponse->assertSee('Meja Kerja Kayu');
    $filteredResponse->assertSee('Kursi Kerja Ergonomis');
    $filteredResponse->assertSee('Bidang Data Aset');
    $filteredResponse->assertDontSee('Aset Bidang Lain');
});
