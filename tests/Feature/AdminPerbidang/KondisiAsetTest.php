<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\RiwayatKondisiRegister;
use App\Models\RiwayatKondisiSmki;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('admin perbidang condition update requires notes and photo evidence', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'KONDISI-VALIDASI-' . uniqid(),
        'nama_bidang' => 'Bidang Kondisi Validasi',
        'nama_ruangan' => 'Ruang Kondisi Validasi',
    ]);

    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-KONDISI-VALIDASI-001',
        'nama_aset' => 'Laptop Validasi Kondisi',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Kondisi Validasi',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin-perbidang.kondisi-aset.edit', ['kondisi_aset' => $asset->id, 'type' => 'REGISTER']))
        ->put(route('admin-perbidang.kondisi-aset.update', $asset->id), [
            'tipe_aset' => 'REGISTER',
            'keadaan_baru' => 'Rusak Ringan',
        ]);

    $response->assertRedirect(route('admin-perbidang.kondisi-aset.edit', ['kondisi_aset' => $asset->id, 'type' => 'REGISTER']));
    $response->assertSessionHasErrors(['catatan', 'foto']);
    expect(RiwayatKondisiRegister::where('aset_register_id', $asset->id)->exists())->toBeFalse();
});

test('admin perbidang condition photo must be an allowed image file', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'KONDISI-FILE-' . uniqid(),
        'nama_bidang' => 'Bidang Kondisi File',
        'nama_ruangan' => 'Ruang Kondisi File',
    ]);

    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-KONDISI-FILE-001',
        'nama_aset' => 'Laptop File Kondisi',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Kondisi File',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->from(route('admin-perbidang.kondisi-aset.edit', ['kondisi_aset' => $asset->id, 'type' => 'REGISTER']))
        ->put(route('admin-perbidang.kondisi-aset.update', $asset->id), [
            'tipe_aset' => 'REGISTER',
            'keadaan_baru' => 'Rusak Ringan',
            'catatan' => 'Kondisi berubah dan perlu pemeriksaan.',
            'foto' => UploadedFile::fake()->create('bukti-kondisi.pdf', 200, 'application/pdf'),
        ]);

    $response->assertRedirect(route('admin-perbidang.kondisi-aset.edit', ['kondisi_aset' => $asset->id, 'type' => 'REGISTER']));
    $response->assertSessionHasErrors(['foto']);
});

test('admin perbidang can update condition with required notes and photo evidence', function () {
    Storage::fake('public');

    $bidang = Bidang::create([
        'kode_bidang' => 'KONDISI-SIMPAN-' . uniqid(),
        'nama_bidang' => 'Bidang Kondisi Simpan',
        'nama_ruangan' => 'Ruang Kondisi Simpan',
    ]);

    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-KONDISI-SIMPAN-001',
        'nama_aset' => 'Laptop Simpan Kondisi',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Kondisi Simpan',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin-perbidang.kondisi-aset.update', $asset->id), [
            'tipe_aset' => 'REGISTER',
            'keadaan_baru' => 'Rusak Ringan',
            'catatan' => 'Foto kondisi sudah dilampirkan untuk pemeriksaan.',
            'foto' => UploadedFile::fake()->createWithContent(
                'bukti-kondisi.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
            ),
        ]);

    $response->assertRedirect(route('admin-perbidang.kondisi-aset.index'));

    $history = RiwayatKondisiRegister::where('aset_register_id', $asset->id)->firstOrFail();
    expect($history->catatan)->toBe('Foto kondisi sudah dilampirkan untuk pemeriksaan.');
    expect($history->foto_path)->not->toBeNull();
    Storage::disk('public')->assertExists($history->foto_path);
});
