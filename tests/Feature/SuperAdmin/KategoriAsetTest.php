<?php

use App\Models\AsetRegister;
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
    $indexResponse->assertSee('Laptop');

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
