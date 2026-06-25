<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('super admin can view verified assets for qr registration', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-QR-001',
        'nama_aset' => 'Switch Core',
        'kode_barang' => 'KB-QR-001',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Server',
        'kerahasiaan' => 'Terbatas',
        'kritikalitas' => 'TINGGI',
        'nilai' => 8000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $adminBidang->id,
        'diverifikasi_oleh' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.qr-code.index'));

    $response->assertOk();
    $response->assertSee('Switch Core');
    $response->assertSee('Belum QR');
});

test('super admin can generate qr code for verified asset', function () {
    Storage::fake('public');

    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-QR-002',
        'nama_aset' => 'Firewall Utama',
        'kode_barang' => 'KB-QR-002',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Server',
        'kerahasiaan' => 'Terbatas',
        'kritikalitas' => 'TINGGI',
        'nilai' => 15000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $adminBidang->id,
        'diverifikasi_oleh' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.qr-code.generate', ['register', $asset->id]));

    $response->assertRedirect(route('super-admin.qr-code.index'));
    $asset->refresh();

    expect($asset->qr_code_path)->toBe('qrcodes/register-' . $asset->id . '.svg');
    Storage::disk('public')->assertExists($asset->qr_code_path);
});

test('generated qr asset shows preview print and download actions', function () {
    Storage::fake('public');

    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'QR-ACTIVE',
        'nama_bidang' => 'Bidang QR Aktif',
        'nama_ruangan' => 'Ruang QR Aktif',
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-QR-ACTIVE',
        'nama_aset' => 'Router QR Aktif',
        'kode_barang' => 'KB-QR-ACTIVE',
        'kode_urut_barang' => '003',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Server',
        'kerahasiaan' => 'Terbatas',
        'kritikalitas' => 'TINGGI',
        'nilai' => 9000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'qr_code_path' => 'qrcodes/register-active.svg',
        'dinput_oleh' => $adminBidang->id,
        'diverifikasi_oleh' => $superAdmin->id,
    ]);
    Storage::disk('public')->put($asset->qr_code_path, '<svg></svg>');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.qr-code.index', ['status_qr' => 'Sudah QR']));

    $response->assertOk();
    $response->assertSee('Router QR Aktif');
    $response->assertSee('Sudah QR');
    $response->assertDontSee(Storage::disk('public')->url($asset->qr_code_path), false);
    $response->assertSee(route('super-admin.qr-code.label', ['register', $asset->id]), false);
    $response->assertSee(route('super-admin.qr-code.download', ['register', $asset->id]), false);
    $response->assertDontSee(route('super-admin.qr-code.generate', ['register', $asset->id]), false);
});

test('qr label and scanned detail can be rendered', function () {
    Storage::fake('public');

    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $adminBidang = User::factory()->create(['role' => 'Admin Perbidang']);
    $bidang = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);

    $asset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-QR-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Sistem Inventaris',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang TIK',
        'penanggung_jawab' => 'Admin Bidang',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $adminBidang->id,
        'diverifikasi_oleh' => $superAdmin->id,
    ]);

    $labelResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.qr-code.label', ['smki', $asset->id]));

    $labelResponse->assertOk();
    $labelResponse->assertSee('Sistem Inventaris');
    $asset->refresh();
    Storage::disk('public')->assertExists($asset->qr_code_path);

    $scanResponse = $this->get(route('qr.asset.show', ['smki', $asset->id]));

    $scanResponse->assertOk();
    $scanResponse->assertSee('Sistem Inventaris');
    $scanResponse->assertSee('SMKI-QR-001');
});
