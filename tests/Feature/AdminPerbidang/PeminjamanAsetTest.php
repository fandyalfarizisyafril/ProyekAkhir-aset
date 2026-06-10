<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PeminjamanAset;
use App\Models\User;

test('admin perbidang can view loan request form with available assets', function () {
    [$bidang, $admin] = peminjamanActors();
    $asset = peminjamanRegisterAsset($bidang->id, $admin->id, 'REG-PINJAM-001');

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.peminjaman-aset.create'));

    $response->assertOk();
    $response->assertSee('Pengajuan Peminjaman Aset');
    $response->assertSee($asset->nama_aset);
});

test('admin perbidang can submit register asset loan request', function () {
    [$bidang, $admin] = peminjamanActors();
    $asset = peminjamanRegisterAsset($bidang->id, $admin->id, 'REG-PINJAM-002');

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.peminjaman-aset.store'), [
            'jenis_aset' => 'register',
            'aset_id' => $asset->id,
            'tanggal_pinjam' => '2026-06-11',
            'tanggal_rencana_kembali' => '2026-06-18',
            'keperluan' => 'Dipinjam untuk kegiatan rapat koordinasi bidang.',
            'catatan' => 'Akan digunakan di ruang rapat utama.',
        ]);

    $response->assertRedirect(route('admin-perbidang.peminjaman-aset.index'));

    $peminjaman = PeminjamanAset::first();
    expect($peminjaman)->not->toBeNull();
    expect($peminjaman->jenis_aset)->toBe('register');
    expect($peminjaman->aset_register_id)->toBe($asset->id);
    expect($peminjaman->peminjam_id)->toBe($admin->id);
    expect($peminjaman->status)->toBe('Menunggu Verifikasi');
    expect($asset->fresh()->status)->toBe('Aktif');
});

test('admin perbidang can submit smki asset loan request', function () {
    [$bidang, $admin] = peminjamanActors();
    $asset = peminjamanSmkiAsset($bidang->id, $admin->id, 'SMKI-PINJAM-001');

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.peminjaman-aset.store'), [
            'jenis_aset' => 'smki',
            'aset_id' => $asset->id,
            'tanggal_pinjam' => '2026-06-11',
            'tanggal_rencana_kembali' => '2026-06-20',
            'keperluan' => 'Aplikasi dipakai untuk demonstrasi layanan internal.',
        ]);

    $response->assertRedirect(route('admin-perbidang.peminjaman-aset.index'));

    $peminjaman = PeminjamanAset::first();
    expect($peminjaman->jenis_aset)->toBe('smki');
    expect($peminjaman->aset_smki_id)->toBe($asset->id);
    expect($peminjaman->status)->toBe('Menunggu Verifikasi');
});

test('admin perbidang cannot request loan for unverified asset', function () {
    [$bidang, $admin] = peminjamanActors();
    $asset = peminjamanRegisterAsset($bidang->id, $admin->id, 'REG-PINJAM-003', 'Perlu Verifikasi');

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.peminjaman-aset.store'), [
            'jenis_aset' => 'register',
            'aset_id' => $asset->id,
            'tanggal_pinjam' => '2026-06-11',
            'tanggal_rencana_kembali' => '2026-06-18',
            'keperluan' => 'Aset ini belum terverifikasi sehingga tidak boleh dipinjam.',
        ]);

    $response->assertNotFound();
    expect(PeminjamanAset::count())->toBe(0);
});

test('admin perbidang cannot request loan for asset with active loan request', function () {
    [$bidang, $admin] = peminjamanActors();
    $asset = peminjamanRegisterAsset($bidang->id, $admin->id, 'REG-PINJAM-004');

    PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'peminjam_id' => $admin->id,
        'tanggal_pinjam' => '2026-06-11',
        'tanggal_rencana_kembali' => '2026-06-18',
        'keperluan' => 'Pengajuan pertama masih aktif.',
        'status' => 'Menunggu Verifikasi',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.peminjaman-aset.store'), [
            'jenis_aset' => 'register',
            'aset_id' => $asset->id,
            'tanggal_pinjam' => '2026-06-12',
            'tanggal_rencana_kembali' => '2026-06-19',
            'keperluan' => 'Pengajuan kedua untuk aset yang sama.',
        ]);

    $response->assertStatus(422);
    expect(PeminjamanAset::count())->toBe(1);
});

function peminjamanActors(): array
{
    $bidang = Bidang::create([
        'kode_bidang' => 'PINJAM-' . uniqid(),
        'nama_bidang' => 'Bidang Peminjaman',
        'nama_ruangan' => 'Ruang Peminjaman',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    return [$bidang, $admin];
}

function peminjamanRegisterAsset(int $bidangId, int $userId, string $code, string $statusVerifikasi = 'Terverifikasi'): AsetRegister
{
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => 'Laptop Peminjaman ' . $code,
        'kode_barang' => 'KB-' . $code,
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Peminjaman',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => $statusVerifikasi,
        'dinput_oleh' => $userId,
    ]);
}

function peminjamanSmkiAsset(int $bidangId, int $userId, string $code): AsetSmki
{
    return AsetSmki::create([
        'nomor_kode_barang' => $code,
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Peminjaman',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidangId,
        'ruangan' => 'Ruang Peminjaman',
        'penanggung_jawab' => 'Admin Bidang',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}
