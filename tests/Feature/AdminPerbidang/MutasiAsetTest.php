<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\User;

test('admin perbidang can view mutation request form', function () {
    $bidangAsal = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);
    $bidangTujuan = Bidang::create([
        'kode_bidang' => 'IKP',
        'nama_bidang' => 'Informasi Komunikasi Publik',
        'nama_ruangan' => 'Ruang IKP',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangAsal->id,
    ]);

    $asset = registerAsset($bidangAsal->id, $admin->id, 'REG-MUT-001');

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.mutasi-aset.create'));

    $response->assertOk();
    $response->assertSee('Pengajuan Mutasi Aset');
    $response->assertSee($asset->nama_aset);
    $response->assertSee($bidangTujuan->nama_bidang);
    $response->assertSee('RENCANA PENGEMBALIAN');
});

test('admin perbidang can submit register asset mutation request', function () {
    $bidangAsal = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);
    $bidangTujuan = Bidang::create([
        'kode_bidang' => 'IKP',
        'nama_bidang' => 'Informasi Komunikasi Publik',
        'nama_ruangan' => 'Ruang IKP',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangAsal->id,
    ]);
    $asset = registerAsset($bidangAsal->id, $admin->id, 'REG-MUT-002');

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.mutasi-aset.store'), [
            'jenis_aset' => 'register',
            'aset_id' => $asset->id,
            'bidang_tujuan_id' => $bidangTujuan->id,
            'tanggal_mutasi' => '2026-06-10',
            'tanggal_rencana_pengembalian' => '2026-06-20',
            'alasan' => 'Aset dibutuhkan untuk operasional bidang tujuan.',
        ]);

    $response->assertRedirect(route('admin-perbidang.mutasi-aset.index'));

    $mutasi = MutasiAset::first();
    expect($mutasi)->not->toBeNull();
    expect($mutasi->jenis_aset)->toBe('register');
    expect($mutasi->aset_register_id)->toBe($asset->id);
    expect($mutasi->bidang_asal_id)->toBe($bidangAsal->id);
    expect($mutasi->bidang_tujuan_id)->toBe($bidangTujuan->id);
    expect($mutasi->tanggal_rencana_pengembalian->toDateString())->toBe('2026-06-20');
    expect($mutasi->status)->toBe('Menunggu Verifikasi');
    expect($asset->fresh()->bidang_id)->toBe($bidangAsal->id);
});

test('admin perbidang can submit smki asset mutation request', function () {
    $bidangAsal = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);
    $bidangTujuan = Bidang::create([
        'kode_bidang' => 'IKP',
        'nama_bidang' => 'Informasi Komunikasi Publik',
        'nama_ruangan' => 'Ruang IKP',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangAsal->id,
    ]);
    $asset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-MUT-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Mutasi',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidangAsal->id,
        'ruangan' => 'Ruang TIK',
        'penanggung_jawab' => 'Admin Bidang',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.mutasi-aset.store'), [
            'jenis_aset' => 'smki',
            'aset_id' => $asset->id,
            'bidang_tujuan_id' => $bidangTujuan->id,
            'tanggal_mutasi' => '2026-06-10',
            'tanggal_rencana_pengembalian' => '2026-06-20',
            'alasan' => 'Aplikasi digunakan oleh bidang tujuan.',
        ]);

    $response->assertRedirect(route('admin-perbidang.mutasi-aset.index'));

    $mutasi = MutasiAset::first();
    expect($mutasi->jenis_aset)->toBe('smki');
    expect($mutasi->aset_smki_id)->toBe($asset->id);
    expect($mutasi->status)->toBe('Menunggu Verifikasi');
});

test('admin perbidang can see mutation impact status', function () {
    $bidangAsal = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);
    $bidangTujuan = Bidang::create([
        'kode_bidang' => 'IKP',
        'nama_bidang' => 'Informasi Komunikasi Publik',
        'nama_ruangan' => 'Ruang IKP',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangAsal->id,
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $asset = registerAsset($bidangTujuan->id, $admin->id, 'REG-MUT-IMPACT');

    $mutasi = MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidangAsal->id,
        'bidang_tujuan_id' => $bidangTujuan->id,
        'alasan' => 'Aset sudah dipindahkan untuk kebutuhan layanan.',
        'status' => 'Disetujui',
        'diajukan_oleh' => $admin->id,
        'disetujui_oleh' => $superAdmin->id,
        'tanggal_mutasi' => '2026-06-10',
        'tanggal_rencana_pengembalian' => '2026-06-20',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.mutasi-aset.index'));

    $response->assertOk();
    $response->assertSee('Aset berpindah ke ' . $bidangTujuan->nama_bidang);

    $detailResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.mutasi-aset.show', $mutasi->id));

    $detailResponse->assertOk();
    $detailResponse->assertSee('Dampak Mutasi');
    $detailResponse->assertSee('Aset berpindah ke ' . $bidangTujuan->nama_bidang);
    $detailResponse->assertSee('Data aset aktif sudah berada di bidang tujuan');
});

test('admin perbidang cannot request mutation for asset outside their bidang', function () {
    $bidangAdmin = Bidang::create([
        'kode_bidang' => 'TIK',
        'nama_bidang' => 'Teknologi Informasi',
        'nama_ruangan' => 'Ruang TIK',
    ]);
    $bidangLain = Bidang::create([
        'kode_bidang' => 'IKP',
        'nama_bidang' => 'Informasi Komunikasi Publik',
        'nama_ruangan' => 'Ruang IKP',
    ]);
    $bidangTujuan = Bidang::create([
        'kode_bidang' => 'INF',
        'nama_bidang' => 'Infrastruktur',
        'nama_ruangan' => 'Ruang Infrastruktur',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangAdmin->id,
    ]);
    $otherAdmin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangLain->id,
    ]);
    $asset = registerAsset($bidangLain->id, $otherAdmin->id, 'REG-MUT-003');

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.mutasi-aset.store'), [
            'jenis_aset' => 'register',
            'aset_id' => $asset->id,
            'bidang_tujuan_id' => $bidangTujuan->id,
            'tanggal_mutasi' => '2026-06-10',
            'tanggal_rencana_pengembalian' => '2026-06-20',
            'alasan' => 'Aset ini tidak berada pada bidang admin.',
        ]);

    $response->assertNotFound();
    expect(MutasiAset::count())->toBe(0);
});

function registerAsset(int $bidangId, int $userId, string $code): AsetRegister
{
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => 'Laptop Mutasi ' . $code,
        'kode_barang' => 'KB-' . $code,
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Kerja',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}
