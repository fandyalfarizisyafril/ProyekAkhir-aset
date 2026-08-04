<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\PermintaanMutasiAset;
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
    $response->assertDontSee('RENCANA PENGEMBALIAN');
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
            'alasan' => 'Aset dibutuhkan untuk operasional bidang tujuan.',
        ]);

    $response->assertRedirect(route('admin-perbidang.mutasi-aset.index'));

    $mutasi = MutasiAset::first();
    expect($mutasi)->not->toBeNull();
    expect($mutasi->jenis_aset)->toBe('register');
    expect($mutasi->aset_register_id)->toBe($asset->id);
    expect($mutasi->bidang_asal_id)->toBe($bidangAsal->id);
    expect($mutasi->bidang_tujuan_id)->toBe($bidangTujuan->id);
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
            'alasan' => 'Aset ini tidak berada pada bidang admin.',
        ]);

    $response->assertNotFound();
    expect(MutasiAset::count())->toBe(0);
});

test('admin perbidang can submit mutation demand without seeing other bidang assets', function () {
    $bidangPeminta = Bidang::create([
        'kode_bidang' => 'REQ-IKP',
        'nama_bidang' => 'IKP',
        'nama_ruangan' => 'Ruang IKP',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangPeminta->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin-perbidang.permintaan-mutasi.create'))
        ->assertOk()
        ->assertSee('Permintaan Mutasi Aset')
        ->assertDontSee('ASET YANG DIMUTASIKAN');

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.permintaan-mutasi.store'), [
            'jenis_aset' => 'register',
            'kategori_aset' => 'Laptop',
            'nama_kebutuhan' => 'Laptop layanan informasi',
            'lokasi_penggunaan' => 'Ruang IKP',
            'tanggal_permintaan' => '2026-07-20',
            'spesifikasi' => 'Minimal RAM 8GB untuk operator layanan.',
            'alasan' => 'Bidang membutuhkan perangkat tambahan untuk layanan publik.',
        ]);

    $response->assertRedirect(route('admin-perbidang.permintaan-mutasi.index'));

    $request = PermintaanMutasiAset::first();
    expect($request)->not->toBeNull();
    expect($request->bidang_peminta_id)->toBe($bidangPeminta->id);
    expect($request->status)->toBe('Menunggu Verifikasi');
    expect(MutasiAset::count())->toBe(0);
});

test('admin perbidang can mark register asset as available for mutation', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'MUT-READY',
        'nama_bidang' => 'Persandian',
        'nama_ruangan' => 'Ruang Persandian',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $asset = registerAsset($bidang->id, $admin->id, 'MUT-READY-REG');

    $this->actingAs($admin)
        ->put(route('admin-perbidang.data-aset-register.update', $asset->id), [
            'kode_aset' => $asset->kode_aset,
            'nama_aset' => $asset->nama_aset,
            'kode_barang' => $asset->kode_barang,
            'kode_urut_barang' => $asset->kode_urut_barang,
            'status_barang' => 'Baik',
            'status' => 'Bisa dimutasi',
            'pemilik_aset' => $asset->pemilik_aset,
            'pengguna' => $asset->pengguna,
            'lokasi_aset' => $asset->lokasi_aset,
            'metode_pemusnahan' => $asset->metode_pemusnahan,
            'kerahasiaan' => $asset->kerahasiaan,
            'kritikalitas' => $asset->kritikalitas,
            'nilai' => $asset->nilai,
            'tanggal_perolehan' => null,
            'keterangan' => $asset->keterangan,
        ])
        ->assertRedirect(route('admin-perbidang.data-aset-register.index'));

    expect($asset->fresh()->status)->toBe('Bisa dimutasi');

    $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index'))
        ->assertOk()
        ->assertSee('Bisa dimutasi')
        ->assertDontSee('Batalkan Mutasi');

    $this->actingAs($admin)
        ->put(route('admin-perbidang.data-aset-register.update', $asset->id), [
            'kode_aset' => $asset->kode_aset,
            'nama_aset' => $asset->nama_aset,
            'kode_barang' => $asset->kode_barang,
            'kode_urut_barang' => $asset->kode_urut_barang,
            'status_barang' => 'Baik',
            'status' => 'Tersedia',
            'pemilik_aset' => $asset->pemilik_aset,
            'pengguna' => $asset->pengguna,
            'lokasi_aset' => $asset->lokasi_aset,
            'metode_pemusnahan' => $asset->metode_pemusnahan,
            'kerahasiaan' => $asset->kerahasiaan,
            'kritikalitas' => $asset->kritikalitas,
            'nilai' => $asset->nilai,
            'tanggal_perolehan' => null,
            'keterangan' => $asset->keterangan,
        ])
        ->assertRedirect(route('admin-perbidang.data-aset-register.index'));

    expect($asset->fresh()->status)->toBe('Tersedia');
});

test('admin perbidang can mark smki asset as available for mutation', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'MUT-SMKI',
        'nama_bidang' => 'Aptika',
        'nama_ruangan' => 'Ruang Aptika',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $asset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-READY-001',
        'jenis_barang' => 'Server',
        'merk_model' => 'Server Aplikasi Mutasi',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Server',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->put(route('admin-perbidang.data-aset-smki.update', $asset->id), [
            'nomor_kode_barang' => $asset->nomor_kode_barang,
            'jenis_barang' => $asset->jenis_barang,
            'merk_model' => $asset->merk_model,
            'no_ser_model' => $asset->no_ser_model,
            'ukuran' => $asset->ukuran,
            'bahan' => $asset->bahan,
            'tahun_pembuatan' => $asset->tahun_pembuatan,
            'jumlah' => $asset->jumlah,
            'satuan' => $asset->satuan,
            'keadaan_barang' => 'Baik',
            'status' => 'Bisa dimutasi',
            'keterangan' => $asset->keterangan,
            'ruangan' => $asset->ruangan,
            'penanggung_jawab' => $asset->penanggung_jawab,
        ])
        ->assertRedirect(route('admin-perbidang.data-aset-smki.index'));

    expect($asset->fresh()->status)->toBe('Bisa dimutasi');
});

test('super admin can fulfill mutation demand by choosing asset from another bidang', function () {
    $bidangAsal = Bidang::create([
        'kode_bidang' => 'REQ-ASAL',
        'nama_bidang' => 'Persandian',
        'nama_ruangan' => 'Ruang Persandian',
    ]);
    $bidangPeminta = Bidang::create([
        'kode_bidang' => 'REQ-TUJUAN',
        'nama_bidang' => 'IKP',
        'nama_ruangan' => 'Ruang IKP',
    ]);
    $adminPeminta = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidangPeminta->id,
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $asset = registerAsset($bidangAsal->id, $superAdmin->id, 'REQ-ASSET-001');
    $asset->update(['status' => 'Bisa dimutasi']);
    $lockedAsset = registerAsset($bidangAsal->id, $superAdmin->id, 'REQ-ASSET-LOCKED');

    $request = PermintaanMutasiAset::create([
        'jenis_aset' => 'register',
        'kategori_aset' => $asset->kode_barang,
        'nama_kebutuhan' => 'Laptop operasional IKP',
        'lokasi_penggunaan' => 'Ruang Layanan IKP',
        'spesifikasi' => 'Butuh perangkat siap pakai.',
        'alasan' => 'Bidang IKP membutuhkan aset tambahan untuk pelayanan.',
        'status' => 'Menunggu Verifikasi',
        'tanggal_permintaan' => '2026-07-21',
        'bidang_peminta_id' => $bidangPeminta->id,
        'diminta_oleh' => $adminPeminta->id,
    ]);

    $this->actingAs($superAdmin)
        ->get(route('super-admin.permintaan-mutasi.show', $request->id))
        ->assertOk()
        ->assertSee('Kandidat Aset')
        ->assertSee($asset->nama_aset)
        ->assertDontSee($lockedAsset->kode_aset)
        ->assertSee($bidangAsal->nama_bidang);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.permintaan-mutasi.fulfill', $request->id), [
            'asset_choice' => 'register:' . $asset->id,
            'catatan_super_admin' => 'Aset tersedia dan sesuai kebutuhan.',
        ]);

    $response->assertRedirect(route('super-admin.permintaan-mutasi.index'));

    $request->refresh();
    $asset->refresh();
    $mutasi = MutasiAset::first();

    expect($request->status)->toBe('Dipenuhi');
    expect($request->mutasi_aset_id)->toBe($mutasi->id);
    expect($asset->bidang_id)->toBe($bidangPeminta->id);
    expect($asset->lokasi_aset)->toBe('Ruang Layanan IKP');
    expect($asset->status)->toBe('Tersedia');
    expect($mutasi->status)->toBe('Disetujui');
    expect($mutasi->bidang_asal_id)->toBe($bidangAsal->id);
    expect($mutasi->bidang_tujuan_id)->toBe($bidangPeminta->id);

    $this->actingAs($superAdmin)
        ->get(route('super-admin.permintaan-mutasi.show', $request->id))
        ->assertOk()
        ->assertSee('Mutasi Terbentuk')
        ->assertSee($asset->nama_aset)
        ->assertSee(route('riwayat-mutasi.show', $mutasi->id), false);

    $this->actingAs($adminPeminta)
        ->get(route('riwayat-mutasi.show', $mutasi->id))
        ->assertOk()
        ->assertSee('Berasal dari Permintaan Mutasi')
        ->assertSee('Laptop operasional IKP')
        ->assertSee(route('admin-perbidang.permintaan-mutasi.show', $request->id), false);
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
