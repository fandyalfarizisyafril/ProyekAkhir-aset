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
    $response->assertSee('Aset berpindah ke ' . $tujuan->nama_bidang);
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
    $response->assertSee('Aset tetap di ' . $asal->nama_bidang);
    $response->assertSee('Pengajuan mutasi tidak mengubah bidang atau lokasi aset.');
});

test('kepala dinas mutation history only shows total summary card', function () {
    [$asal, $tujuan, $adminAsal, , $kepalaDinas] = f12MutationActors();
    f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-KEPALA-CARD-001', 'Disetujui');
    f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-KEPALA-CARD-002', 'Ditolak');

    $response = $this->actingAs($kepalaDinas)
        ->get(route('riwayat-mutasi.index'));

    $response->assertOk();
    $response->assertSee('Total Riwayat');
    $response->assertSee('bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-xl', false);
    $response->assertViewHas('isKepalaDinas', true);
    $response->assertDontSee('bg-white rounded-2xl border border-slate-100 shadow-sm p-4 sm:p-6 flex items-center gap-3', false);
    $response->assertDontSee('Dalam verifikasi');
    $response->assertDontSee('Tidak berpindah');
});

test('unsupported role cannot access mutation history', function () {
    [$asal, $tujuan, $adminAsal] = f12MutationActors();
    $pending = f12RegisterMutation($asal, $tujuan, $adminAsal, 'F12-UNSUPPORTED-PENDING', 'Menunggu Verifikasi');
    $unsupportedUser = User::factory()->create(['role' => 'Operator Lama']);

    $response = $this->actingAs($unsupportedUser)
        ->get(route('riwayat-mutasi.index'));

    $response->assertForbidden();

    $detailResponse = $this->actingAs($unsupportedUser)
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
