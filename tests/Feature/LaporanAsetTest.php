<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\Laporan;
use App\Models\PenghapusanAset;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeRegisterReportAsset(array $overrides = []): AsetRegister
{
    $asset = AsetRegister::create(array_merge([
        'kode_aset' => 'REG-REPORT-' . uniqid(),
        'nama_aset' => 'Aset Register Laporan',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $overrides['bidang_id'] ?? Bidang::factory()->create()->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Laporan',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 7000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $overrides['dinput_oleh'] ?? User::factory()->create()->id,
    ], $overrides));

    if (isset($overrides['created_at'])) {
        $asset->forceFill([
            'created_at' => $overrides['created_at'],
            'updated_at' => $overrides['created_at'],
        ])->save();
    }

    return $asset;
}

function makeSmkiReportAsset(array $overrides = []): AsetSmki
{
    $asset = AsetSmki::create(array_merge([
        'nomor_kode_barang' => 'SMKI-REPORT-' . uniqid(),
        'jenis_barang' => 'Server',
        'merk_model' => 'Aset SMKI Laporan',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $overrides['bidang_id'] ?? Bidang::factory()->create()->id,
        'ruangan' => 'Ruang Laporan',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $overrides['dinput_oleh'] ?? User::factory()->create()->id,
    ], $overrides));

    if (isset($overrides['created_at'])) {
        $asset->forceFill([
            'created_at' => $overrides['created_at'],
            'updated_at' => $overrides['created_at'],
        ])->save();
    }

    return $asset;
}

test('super admin can view filtered asset report across bidang', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-SA-' . uniqid(),
        'nama_bidang' => 'Bidang Laporan Super Admin',
        'nama_ruangan' => 'Ruang Laporan Super Admin',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'REPORT-SA-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang Laporan Lain',
        'nama_ruangan' => 'Ruang Laporan Lain',
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $inputter = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $register = makeRegisterReportAsset([
        'kode_aset' => 'REG-LAPORAN-001',
        'nama_aset' => 'Laptop Masuk Laporan',
        'kode_barang' => 'Laptop',
        'bidang_id' => $bidang->id,
        'dinput_oleh' => $inputter->id,
        'created_at' => Carbon::parse('2026-06-20 08:00:00'),
    ]);
    makeSmkiReportAsset([
        'nomor_kode_barang' => 'SMKI-LAPORAN-OTHER',
        'merk_model' => 'Server Bidang Lain',
        'jenis_barang' => 'Server',
        'bidang_id' => $otherBidang->id,
        'dinput_oleh' => $inputter->id,
        'created_at' => Carbon::parse('2026-06-20 09:00:00'),
    ]);
    makeRegisterReportAsset([
        'kode_aset' => 'REG-LAPORAN-PENDING',
        'nama_aset' => 'Aset Pending Tidak Masuk',
        'kode_barang' => 'Laptop',
        'bidang_id' => $bidang->id,
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $inputter->id,
        'created_at' => Carbon::parse('2026-06-20 10:00:00'),
    ]);
    $deleted = makeRegisterReportAsset([
        'kode_aset' => 'REG-LAPORAN-DELETED',
        'nama_aset' => 'Aset Dihapus Tidak Masuk',
        'kode_barang' => 'Laptop',
        'bidang_id' => $bidang->id,
        'status' => 'Dihapus',
        'dinput_oleh' => $inputter->id,
        'created_at' => Carbon::parse('2026-06-20 11:00:00'),
    ]);
    PenghapusanAset::create([
        'aset_register_id' => $deleted->id,
        'jenis_aset' => 'register',
        'kode_aset' => $deleted->kode_aset,
        'nama_aset' => $deleted->nama_aset,
        'bidang_id' => $bidang->id,
        'nilai_buku' => 1000000,
        'tanggal_penghapusan' => '2026-06-20',
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Audit laporan',
        'dihapus_oleh' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('laporan-aset.index', [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'bidang_id' => $bidang->id,
            'kategori' => 'Laptop',
            'kondisi' => 'Baik',
        ]));

    $response->assertOk();
    $response->assertSee('Laporan Aset');
    $response->assertSee('Laptop Masuk Laporan');
    $response->assertSee('REG-LAPORAN-001');
    $response->assertSee('Rp 7.000.000');
    $response->assertSee('1 aset nonaktif pada periode');
    $response->assertDontSee('Server Bidang Lain');
    $response->assertDontSee('Aset Pending Tidak Masuk');
    $response->assertDontSee('Aset Dihapus Tidak Masuk');

    expect($register->exists)->toBeTrue();
});

test('admin perbidang report is scoped to own bidang even when another bidang is requested', function () {
    $ownBidang = Bidang::create([
        'kode_bidang' => 'REPORT-ADMIN-' . uniqid(),
        'nama_bidang' => 'Bidang Laporan Admin',
        'nama_ruangan' => 'Ruang Laporan Admin',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'REPORT-ADMIN-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang Admin Lain',
        'nama_ruangan' => 'Ruang Admin Lain',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $ownBidang->id,
    ]);

    makeRegisterReportAsset([
        'kode_aset' => 'REG-ADMIN-LAPORAN',
        'nama_aset' => 'Aset Bidang Admin Sendiri',
        'kode_barang' => 'Meja',
        'bidang_id' => $ownBidang->id,
        'dinput_oleh' => $admin->id,
        'created_at' => Carbon::parse('2026-06-21 08:00:00'),
    ]);
    makeRegisterReportAsset([
        'kode_aset' => 'REG-ADMIN-OTHER',
        'nama_aset' => 'Aset Bidang Admin Lain',
        'kode_barang' => 'Meja',
        'bidang_id' => $otherBidang->id,
        'dinput_oleh' => $admin->id,
        'created_at' => Carbon::parse('2026-06-21 09:00:00'),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('laporan-aset.index', [
            'bidang_id' => $otherBidang->id,
            'kategori' => 'Meja',
        ]));

    $response->assertOk();
    $response->assertSee('Aset Bidang Admin Sendiri');
    $response->assertDontSee('Aset Bidang Admin Lain');
});

test('admin perbidang can upload report document for kepala dinas', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-KD-' . uniqid(),
        'nama_bidang' => 'Bidang Laporan Kepala Dinas',
        'nama_ruangan' => 'Ruang Laporan Kepala Dinas',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    Storage::fake('local');

    $response = $this->actingAs($admin)
        ->post(route('laporan-aset.store'), [
            'jenis_aset' => 'Register',
            'jenis_laporan' => 'Laporan Bulanan',
            'keterangan' => 'Rekap laporan aset bulan Juni.',
            'file' => UploadedFile::fake()->create('rekap-juni.pdf', 64, 'application/pdf'),
        ]);

    $response->assertRedirect(route('laporan-aset.index'));
    $this->assertDatabaseHas('laporan', [
        'jenis_aset' => 'Register',
        'jenis_laporan' => 'Laporan Bulanan',
        'dibuat_oleh' => $admin->id,
        'file_original_name' => 'rekap-juni.pdf',
    ]);

    $report = Laporan::latest()->first();
    Storage::disk('local')->assertExists($report->file_path);
});

test('kepala dinas sees uploaded report documents and can view or download them', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-UPLOAD-' . uniqid(),
        'nama_bidang' => 'Bidang Upload Laporan',
        'nama_ruangan' => 'Ruang Upload Laporan',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);
    Storage::fake('local');
    Storage::disk('local')->put('laporan-aset/rekap-juni.pdf', 'Isi dokumen laporan aset.');

    $report = Laporan::create([
        'jenis_aset' => 'Register',
        'jenis_laporan' => 'Laporan Bulanan',
        'dibuat_oleh' => $admin->id,
        'keterangan' => 'Rekap laporan aset bulan Juni.',
        'file_path' => 'laporan-aset/rekap-juni.pdf',
        'file_original_name' => 'rekap-juni.pdf',
        'file_mime_type' => 'application/pdf',
        'file_size' => 128,
    ]);

    $indexResponse = $this->actingAs($kepalaDinas)
        ->get(route('laporan-aset.index'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Daftar Rekap Laporan');
    $indexResponse->assertSee('Laporan Bulanan');
    $indexResponse->assertSee('rekap-juni.pdf');
    $indexResponse->assertSee('Bidang Upload Laporan');
    $indexResponse->assertDontSee('Daftar Rekap Aset');

    $viewResponse = $this->actingAs($kepalaDinas)
        ->get(route('laporan-aset.view', $report));

    $viewResponse->assertOk();
    expect($viewResponse->headers->get('content-disposition'))->toContain('inline');

    $downloadResponse = $this->actingAs($kepalaDinas)
        ->get(route('laporan-aset.download', $report));

    $downloadResponse->assertOk();
    expect($downloadResponse->headers->get('content-disposition'))->toContain('attachment');
});

test('kepala dinas cannot generate raw asset report directly', function () {
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);

    $this->actingAs($kepalaDinas)
        ->get(route('laporan-aset.export'))
        ->assertForbidden();

    $this->actingAs($kepalaDinas)
        ->get(route('laporan-aset.print'))
        ->assertForbidden();
});
