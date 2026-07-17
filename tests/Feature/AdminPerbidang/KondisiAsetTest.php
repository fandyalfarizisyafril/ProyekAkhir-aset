<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\RiwayatKondisiRegister;
use App\Models\RiwayatKondisiSmki;
use App\Models\User;

test('admin perbidang can open condition history from condition asset list', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'KONDISI-HISTORY-' . uniqid(),
        'nama_bidang' => 'Bidang Kondisi History',
        'nama_ruangan' => 'Ruang Kondisi History',
    ]);

    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $registerAsset = AsetRegister::create([
        'kode_aset' => 'REG-KONDISI-001',
        'nama_aset' => 'Laptop Riwayat Kondisi',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Kondisi History',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Rusak Ringan',
        'status' => 'Maintenance',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $oldRegisterHistory = RiwayatKondisiRegister::create([
        'aset_register_id' => $registerAsset->id,
        'keadaan_lama' => 'Baik',
        'keadaan_baru' => 'Rusak Ringan',
        'catatan' => 'Kondisi register lama.',
        'foto_path' => 'foto_kondisi/register-old.jpg',
        'diupdate_oleh' => $admin->id,
    ]);
    $oldRegisterHistory->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();

    RiwayatKondisiRegister::create([
        'aset_register_id' => $registerAsset->id,
        'keadaan_lama' => 'Rusak Ringan',
        'keadaan_baru' => 'Baik',
        'catatan' => 'Kondisi register terbaru.',
        'foto_path' => 'foto_kondisi/register-latest.jpg',
        'diupdate_oleh' => $admin->id,
    ]);

    $smkiAsset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-KONDISI-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Riwayat Kondisi',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Rusak Berat',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Kondisi History',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Rusak',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    RiwayatKondisiSmki::create([
        'aset_smki_id' => $smkiAsset->id,
        'keadaan_lama' => 'Baik',
        'keadaan_baru' => 'Rusak Berat',
        'catatan' => 'Kondisi SMKI terbaru.',
        'foto_path' => 'foto_kondisi/smki-latest.jpg',
        'diupdate_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.kondisi-aset.index'));

    $response->assertOk();
    $response->assertSee('Daftar Kondisi Aset');
    $response->assertSee('Lihat riwayat kondisi');
    $response->assertSee('Riwayat Kondisi Aset');
    $response->assertSee('Laptop Riwayat Kondisi');
    $response->assertSee('Kondisi register terbaru.');
    $response->assertSee('Kondisi register lama.');
    $response->assertSee('storage/foto_kondisi/register-latest.jpg');
    $response->assertSee('storage/foto_kondisi/register-old.jpg');
    $response->assertSee('Aplikasi Riwayat Kondisi');
    $response->assertSee('Kondisi SMKI terbaru.');
    $response->assertSee('storage/foto_kondisi/smki-latest.jpg');
});
