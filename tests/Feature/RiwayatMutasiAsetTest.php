<?php

use App\Models\AsetRegister;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\User;

test('super admin can view all asset mutation history', function () {
    [$asal, $tujuan, $adminAsal, $superAdmin] = f12MutationActors();
    $history = f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-SUPER-001', 'Disetujui');

    $response = $this->actingAs($superAdmin)
        ->get(route('riwayat-mutasi.index'));

    $response->assertOk();
    $response->assertSee('Riwayat Mutasi Aset');
    $response->assertSee($history->asetRegister->nama_aset);
    $response->assertSee($asal->nama_bidang);
    $response->assertSee($tujuan->nama_bidang);
});

test('admin perbidang only sees mutation history related to their bidang', function () {
    [$asal, $tujuan, $adminAsal] = f12MutationActors();
    $related = f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-ADMIN-RELATED', 'Disetujui');

    $otherAsal = f12Bidang('F12-OTHER-A');
    $otherTujuan = f12Bidang('F12-OTHER-B');
    $otherAdmin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $otherAsal->id,
    ]);
    $unrelated = f12RegisterMutation($otherAsal, $otherTujuan, $otherAdmin, 'F12-ADMIN-UNRELATED', 'Disetujui');

    $response = $this->actingAs($adminAsal)
        ->get(route('riwayat-mutasi.index'));

    $response->assertOk();
    $response->assertSee($related->asetRegister->nama_aset);
    $response->assertDontSee($unrelated->asetRegister->nama_aset);

    $detailResponse = $this->actingAs($adminAsal)
        ->get(route('riwayat-mutasi.show', $unrelated->id));

    $detailResponse->assertForbidden();
});

test('kepala dinas can view mutation history detail', function () {
    [$asal, $tujuan, $adminAsal, , $kepalaDinas] = f12MutationActors();
    $history = f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-KEPALA-001', 'Ditolak');

    $response = $this->actingAs($kepalaDinas)
        ->get(route('riwayat-mutasi.show', $history->id));

    $response->assertOk();
    $response->assertSee('Detail Riwayat Mutasi');
    $response->assertSee($history->asetRegister->nama_aset);
    $response->assertSee('Ditolak');
});

test('regular user only sees approved mutation history', function () {
    [$asal, $tujuan, $adminAsal] = f12MutationActors();
    $approved = f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-USER-APPROVED', 'Disetujui');
    $pending = f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-USER-PENDING', 'Menunggu Verifikasi');
    $user = User::factory()->create(['role' => 'User']);

    $response = $this->actingAs($user)
        ->get(route('riwayat-mutasi.index'));

    $response->assertOk();
    $response->assertSee($approved->asetRegister->nama_aset);
    $response->assertDontSee($pending->asetRegister->nama_aset);

    $detailResponse = $this->actingAs($user)
        ->get(route('riwayat-mutasi.show', $pending->id));

    $detailResponse->assertForbidden();
});

function f12MutationActors(): array
{
    $asal = f12Bidang('F12-ASAL');
    $tujuan = f12Bidang('F12-TUJUAN');
    $adminAsal = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $asal->id,
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);

    return [$asal, $tujuan, $adminAsal, $superAdmin, $kepalaDinas];
}

function f12Bidang(string $prefix): Bidang
{
    $code = $prefix . '-' . uniqid();

    return Bidang::create([
        'kode_bidang' => $code,
        'nama_bidang' => 'Bidang ' . $code,
        'nama_ruangan' => 'Ruang ' . $code,
    ]);
}

function f12RegisterMutation(Bidang $asal, Bidang $tujuan, User $admin, string $code, string $status): MutasiAset
{
    $asset = AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => 'Aset Riwayat Mutasi ' . $code,
        'kode_barang' => 'KB-' . $code,
        'kode_urut_barang' => '001',
        'bidang_id' => $status === 'Disetujui' ? $tujuan->id : $asal->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => $status === 'Disetujui' ? $tujuan->nama_ruangan : $asal->nama_ruangan,
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    return MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $asal->id,
        'bidang_tujuan_id' => $tujuan->id,
        'alasan' => 'Riwayat mutasi untuk pengujian F-12.',
        'status' => $status,
        'diajukan_oleh' => $admin->id,
        'disetujui_oleh' => $status === 'Menunggu Verifikasi' ? null : User::factory()->create(['role' => 'Super Admin'])->id,
        'tanggal_mutasi' => '2026-06-10',
    ]);
}
