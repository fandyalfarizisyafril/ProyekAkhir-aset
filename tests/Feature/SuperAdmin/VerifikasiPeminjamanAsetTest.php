<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PeminjamanAset;
use App\Models\User;

test('super admin can view asset loan verification queue', function () {
    [$bidang, $admin, $superAdmin] = f14PeminjamanActors();
    $asset = f14RegisterAsset($bidang->id, $admin->id, 'REG-VER-PINJAM-001');

    PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'peminjam_id' => $admin->id,
        'tanggal_pinjam' => '2026-06-11',
        'tanggal_rencana_kembali' => '2026-06-18',
        'keperluan' => 'Dipinjam untuk kegiatan koordinasi bidang.',
        'status' => 'Menunggu Verifikasi',
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.verifikasi-peminjaman.index'));

    $response->assertOk();
    $response->assertSee('Verifikasi Peminjaman Aset');
    $response->assertSee($asset->nama_aset);
    $response->assertSee('Menunggu Verifikasi');
});

test('super admin can approve register loan and mark asset as borrowed', function () {
    [$bidang, $admin, $superAdmin] = f14PeminjamanActors();
    $asset = f14RegisterAsset($bidang->id, $admin->id, 'REG-VER-PINJAM-002');

    $peminjaman = PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'peminjam_id' => $admin->id,
        'tanggal_pinjam' => '2026-06-11',
        'tanggal_rencana_kembali' => '2026-06-18',
        'keperluan' => 'Dipinjam untuk kegiatan koordinasi bidang.',
        'status' => 'Menunggu Verifikasi',
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-peminjaman.approve', $peminjaman->id));

    $response->assertRedirect(route('super-admin.verifikasi-peminjaman.index'));

    expect($peminjaman->fresh()->status)->toBe('Disetujui');
    expect($peminjaman->fresh()->disetujui_oleh)->toBe($superAdmin->id);
    expect($asset->fresh()->status)->toBe('Dipinjam');
});

test('super admin can reject smki loan without marking asset as borrowed', function () {
    [$bidang, $admin, $superAdmin] = f14PeminjamanActors();
    $asset = f14SmkiAsset($bidang->id, $admin->id, 'SMKI-VER-PINJAM-001');

    $peminjaman = PeminjamanAset::create([
        'jenis_aset' => 'smki',
        'aset_smki_id' => $asset->id,
        'peminjam_id' => $admin->id,
        'tanggal_pinjam' => '2026-06-11',
        'tanggal_rencana_kembali' => '2026-06-18',
        'keperluan' => 'Dipinjam untuk demo aplikasi internal.',
        'status' => 'Menunggu Verifikasi',
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-peminjaman.reject', $peminjaman->id));

    $response->assertRedirect(route('super-admin.verifikasi-peminjaman.index'));

    expect($peminjaman->fresh()->status)->toBe('Ditolak');
    expect($peminjaman->fresh()->disetujui_oleh)->toBe($superAdmin->id);
    expect($asset->fresh()->status)->toBe('Tersedia');
});

test('super admin cannot process loan request that has already been decided', function () {
    [$bidang, $admin, $superAdmin] = f14PeminjamanActors();
    $asset = f14RegisterAsset($bidang->id, $admin->id, 'REG-VER-PINJAM-003');

    $peminjaman = PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'peminjam_id' => $admin->id,
        'tanggal_pinjam' => '2026-06-11',
        'tanggal_rencana_kembali' => '2026-06-18',
        'keperluan' => 'Pengajuan sudah pernah diproses.',
        'status' => 'Disetujui',
        'disetujui_oleh' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-peminjaman.reject', $peminjaman->id));

    $response->assertRedirect(route('super-admin.verifikasi-peminjaman.index'));
    $response->assertSessionHas('error');

    expect($peminjaman->fresh()->status)->toBe('Disetujui');
    expect($asset->fresh()->status)->toBe('Tersedia');
});

function f14PeminjamanActors(): array
{
    $bidang = Bidang::create([
        'kode_bidang' => 'F14-PINJAM-' . uniqid(),
        'nama_bidang' => 'Bidang Verifikasi Peminjaman',
        'nama_ruangan' => 'Ruang Verifikasi Peminjaman',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);

    return [$bidang, $admin, $superAdmin];
}

function f14RegisterAsset(int $bidangId, int $userId, string $code): AsetRegister
{
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => 'Laptop Verifikasi Peminjaman ' . $code,
        'kode_barang' => 'KB-' . $code,
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Verifikasi Peminjaman',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}

function f14SmkiAsset(int $bidangId, int $userId, string $code): AsetSmki
{
    return AsetSmki::create([
        'nomor_kode_barang' => $code,
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Verifikasi Peminjaman',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidangId,
        'ruangan' => 'Ruang Verifikasi Peminjaman',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}
