<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\User;

test('super admin can view asset mutation verification queue', function () {
    [$bidangAsal, $bidangTujuan, $adminBidang, $superAdmin] = superAdminMutationActors();
    $asset = superAdminMutationRegisterAsset($bidangAsal->id, $adminBidang->id, 'REG-VER-MUT-001');

    MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidangAsal->id,
        'bidang_tujuan_id' => $bidangTujuan->id,
        'alasan' => 'Perlu dipindahkan untuk operasional bidang tujuan.',
        'status' => 'Menunggu Verifikasi',
        'diajukan_oleh' => $adminBidang->id,
        'tanggal_mutasi' => '2026-06-10',
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.verifikasi-mutasi.index'));

    $response->assertOk();
    $response->assertSee('Verifikasi Mutasi Aset');
    $response->assertSee($asset->nama_aset);
    $response->assertSee('Menunggu Verifikasi');
});

test('super admin can approve register mutation and move asset automatically', function () {
    [$bidangAsal, $bidangTujuan, $adminBidang, $superAdmin] = superAdminMutationActors();
    $asset = superAdminMutationRegisterAsset($bidangAsal->id, $adminBidang->id, 'REG-VER-MUT-002');

    $mutasi = MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidangAsal->id,
        'bidang_tujuan_id' => $bidangTujuan->id,
        'alasan' => 'Laptop akan digunakan oleh bidang tujuan.',
        'status' => 'Menunggu Verifikasi',
        'diajukan_oleh' => $adminBidang->id,
        'tanggal_mutasi' => '2026-06-10',
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-mutasi.approve', $mutasi->id));

    $response->assertRedirect(route('super-admin.verifikasi-mutasi.index'));

    expect($mutasi->fresh()->status)->toBe('Disetujui');
    expect($mutasi->fresh()->disetujui_oleh)->toBe($superAdmin->id);
    expect($asset->fresh()->bidang_id)->toBe($bidangTujuan->id);
    expect($asset->fresh()->lokasi_aset)->toBe($bidangTujuan->nama_ruangan);
});

test('super admin can reject smki mutation without moving asset', function () {
    [$bidangAsal, $bidangTujuan, $adminBidang, $superAdmin] = superAdminMutationActors();
    $asset = superAdminMutationSmkiAsset($bidangAsal->id, $adminBidang->id, 'SMKI-VER-MUT-001');

    $mutasi = MutasiAset::create([
        'jenis_aset' => 'smki',
        'aset_smki_id' => $asset->id,
        'bidang_asal_id' => $bidangAsal->id,
        'bidang_tujuan_id' => $bidangTujuan->id,
        'alasan' => 'Aplikasi diminta oleh bidang tujuan.',
        'status' => 'Menunggu Verifikasi',
        'diajukan_oleh' => $adminBidang->id,
        'tanggal_mutasi' => '2026-06-10',
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-mutasi.reject', $mutasi->id));

    $response->assertRedirect(route('super-admin.verifikasi-mutasi.index'));

    expect($mutasi->fresh()->status)->toBe('Ditolak');
    expect($mutasi->fresh()->disetujui_oleh)->toBe($superAdmin->id);
    expect($asset->fresh()->bidang_id)->toBe($bidangAsal->id);
    expect($asset->fresh()->ruangan)->toBe('Ruang Asal Mutasi');
});

test('super admin cannot process mutation that has already been decided', function () {
    [$bidangAsal, $bidangTujuan, $adminBidang, $superAdmin] = superAdminMutationActors();
    $asset = superAdminMutationRegisterAsset($bidangAsal->id, $adminBidang->id, 'REG-VER-MUT-003');

    $mutasi = MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidangAsal->id,
        'bidang_tujuan_id' => $bidangTujuan->id,
        'alasan' => 'Pengajuan ini sudah pernah diproses.',
        'status' => 'Disetujui',
        'diajukan_oleh' => $adminBidang->id,
        'disetujui_oleh' => $superAdmin->id,
        'tanggal_mutasi' => '2026-06-10',
    ]);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-mutasi.reject', $mutasi->id));

    $response->assertRedirect(route('super-admin.verifikasi-mutasi.index'));
    $response->assertSessionHas('error');

    expect($mutasi->fresh()->status)->toBe('Disetujui');
    expect($asset->fresh()->bidang_id)->toBe($bidangAsal->id);
});

function superAdminMutationActors(): array
{
    $bidangAsal = Bidang::create([
        'kode_bidang' => 'MUT-ASAL-' . uniqid(),
        'nama_bidang' => 'Bidang Asal Mutasi',
        'nama_ruangan' => 'Ruang Asal Mutasi',
    ]);
    $bidangTujuan = Bidang::create([
        'kode_bidang' => 'MUT-TUJUAN-' . uniqid(),
        'nama_bidang' => 'Bidang Tujuan Mutasi',
        'nama_ruangan' => 'Ruang Tujuan Mutasi',
    ]);
    $adminBidang = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangAsal->id,
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);

    return [$bidangAsal, $bidangTujuan, $adminBidang, $superAdmin];
}

function superAdminMutationRegisterAsset(int $bidangId, int $userId, string $code): AsetRegister
{
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => 'Laptop Verifikasi Mutasi ' . $code,
        'kode_barang' => 'KB-' . $code,
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Asal Mutasi',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}

function superAdminMutationSmkiAsset(int $bidangId, int $userId, string $code): AsetSmki
{
    return AsetSmki::create([
        'nomor_kode_barang' => $code,
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Verifikasi Mutasi',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidangId,
        'ruangan' => 'Ruang Asal Mutasi',
        'penanggung_jawab' => 'Admin Bidang',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}
