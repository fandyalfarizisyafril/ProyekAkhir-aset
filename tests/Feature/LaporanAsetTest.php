<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\Laporan;
use App\Models\PenghapusanAset;
use App\Models\PenyusutanAset;
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
    PenyusutanAset::create([
        'aset_register_id' => $register->id,
        'tahun' => 2026,
        'umur_manfaat_tahun' => 4,
        'nilai_awal_tahun' => 7000000,
        'nilai_residu' => 0,
        'beban_penyusutan' => 1750000,
        'nilai_akhir_tahun' => 5250000,
        'metode' => 'Garis Lurus',
        'dihitung_oleh' => $superAdmin->id,
        'tanggal_hitung' => '2026-06-30 12:00:00',
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
    $response->assertSee('Upload Laporan');
    $response->assertSee(route('upload-laporan.index'), false);
    $response->assertDontSee('Form Upload Laporan');
    $response->assertDontSee('Daftar Laporan Terupload');
    $response->assertSee('Laptop Masuk Laporan');
    $response->assertSee('REG-LAPORAN-001');
    $response->assertSee('Nilai Perolehan');
    $response->assertSee('Tahun Ke');
    $response->assertSee('Beban Penyusutan');
    $response->assertSee('Akumulasi Penyusutan');
    $response->assertSee('Nilai Buku');
    $response->assertSee('Rp 7.000.000');
    $response->assertSee('Tahun ke-1');
    $response->assertSee('Rp 1.750.000');
    $response->assertSee('Rp 5.250.000');
    $response->assertSee('Penyusutan 2026');
    $response->assertSee('1 aset nonaktif pada periode');
    $response->assertDontSee('Server Bidang Lain');
    $response->assertDontSee('Aset Pending Tidak Masuk');
    $response->assertDontSee('Aset Dihapus Tidak Masuk');

    $reportFilters = [
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'bidang_id' => $bidang->id,
        'kategori' => 'Laptop',
        'kondisi' => 'Baik',
    ];
    $exportResponse = $this->actingAs($superAdmin)
        ->get(route('laporan-aset.export', $reportFilters));

    $exportResponse->assertOk();
    $exportContent = $exportResponse->streamedContent();
    expect($exportContent)
        ->toContain('Nilai Perolehan')
        ->toContain('Tahun Ke')
        ->toContain('Beban Penyusutan')
        ->toContain('Akumulasi Penyusutan')
        ->toContain('Nilai Buku')
        ->toContain('7000000')
        ->toContain('1')
        ->toContain('1750000')
        ->toContain('5250000');

    $printResponse = $this->actingAs($superAdmin)
        ->get(route('laporan-aset.print', $reportFilters));

    $printResponse->assertOk();
    $printResponse->assertSee('Tahun Penyusutan');
    $printResponse->assertSee('Akumulasi Penyusutan');
    $printResponse->assertSee('Tahun ke-1');
    $printResponse->assertSee('Rp 7.000.000');
    $printResponse->assertSee('Rp 1.750.000');
    $printResponse->assertSee('Rp 5.250.000');

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
    $response->assertSee('Upload Laporan');
    $response->assertSee(route('upload-laporan.index'), false);
    $response->assertDontSee('Form Upload Laporan');
    $response->assertDontSee('Daftar Laporan Terupload');
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

    $this->actingAs($admin)
        ->get(route('upload-laporan.index'))
        ->assertOk()
        ->assertSee('Upload Laporan')
        ->assertSee('Form Upload Laporan');

    $response = $this->actingAs($admin)
        ->post(route('upload-laporan.store'), [
            'jenis_aset' => 'Register',
            'jenis_laporan' => 'Laporan Bulanan',
            'keterangan' => 'Rekap laporan aset bulan Juni.',
            'file' => UploadedFile::fake()->create('rekap-juni.pdf', 64, 'application/pdf'),
        ]);

    $response->assertRedirect(route('upload-laporan.index'));
    $this->assertDatabaseHas('laporan', [
        'jenis_aset' => 'Register',
        'jenis_laporan' => 'Laporan Bulanan',
        'dibuat_oleh' => $admin->id,
        'file_original_name' => 'rekap-juni.pdf',
    ]);

    $report = Laporan::latest()->first();
    Storage::disk('local')->assertExists($report->file_path);
});

test('uploaded report requires description and file', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-VALIDASI-' . uniqid(),
        'nama_bidang' => 'Bidang Validasi Laporan',
        'nama_ruangan' => 'Ruang Validasi Laporan',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $response = $this->actingAs($admin)
        ->from(route('upload-laporan.index'))
        ->post(route('upload-laporan.store'), [
            'jenis_aset' => 'Register',
            'jenis_laporan' => 'Laporan Bulanan',
        ]);

    $response->assertRedirect(route('upload-laporan.index'));
    $response->assertSessionHasErrors(['keterangan', 'file']);
    expect(Laporan::count())->toBe(0);
});

test('uploaded report rejects unsupported document formats', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-FORMAT-' . uniqid(),
        'nama_bidang' => 'Bidang Format Laporan',
        'nama_ruangan' => 'Ruang Format Laporan',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $response = $this->actingAs($admin)
        ->from(route('upload-laporan.index'))
        ->post(route('upload-laporan.store'), [
            'jenis_aset' => 'Register',
            'jenis_laporan' => 'Laporan Bulanan',
            'keterangan' => 'Dokumen laporan dengan format tidak didukung.',
            'file' => UploadedFile::fake()->create(
                'rekap-juni.docx',
                64,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ),
        ]);

    $response->assertRedirect(route('upload-laporan.index'));
    $response->assertSessionHasErrors(['file']);
    expect(Laporan::count())->toBe(0);
});

test('admin perbidang can upload excel report document for kepala dinas', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-XLSX-' . uniqid(),
        'nama_bidang' => 'Bidang Laporan Excel',
        'nama_ruangan' => 'Ruang Laporan Excel',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    Storage::fake('local');

    $response = $this->actingAs($admin)
        ->post(route('upload-laporan.store'), [
            'jenis_aset' => 'Register',
            'jenis_laporan' => 'Laporan Bulanan',
            'keterangan' => 'Rekap laporan aset Excel bulan Juni.',
            'file' => UploadedFile::fake()->create(
                'rekap-juni.xlsx',
                64,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ),
        ]);

    $response->assertRedirect(route('upload-laporan.index'));
    $this->assertDatabaseHas('laporan', [
        'jenis_aset' => 'Register',
        'jenis_laporan' => 'Laporan Bulanan',
        'dibuat_oleh' => $admin->id,
        'file_original_name' => 'rekap-juni.xlsx',
    ]);

    $report = Laporan::latest()->first();
    Storage::disk('local')->assertExists($report->file_path);
});

test('admin perbidang can upload exported xls report document for kepala dinas', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-XLS-' . uniqid(),
        'nama_bidang' => 'Bidang Laporan XLS',
        'nama_ruangan' => 'Ruang Laporan XLS',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    Storage::fake('local');

    $response = $this->actingAs($admin)
        ->post(route('upload-laporan.store'), [
            'jenis_aset' => 'Register',
            'jenis_laporan' => 'Laporan Bulanan',
            'keterangan' => 'Rekap laporan aset XLS hasil export sistem.',
            'file' => UploadedFile::fake()->create(
                'laporan-aset-export.xls',
                64,
                'text/html'
            ),
        ]);

    $response->assertRedirect(route('upload-laporan.index'));
    $this->assertDatabaseHas('laporan', [
        'jenis_aset' => 'Register',
        'jenis_laporan' => 'Laporan Bulanan',
        'dibuat_oleh' => $admin->id,
        'file_original_name' => 'laporan-aset-export.xls',
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
    $indexResponse->assertDontSee(route('upload-laporan.index'), false);
    $indexResponse->assertSee('Laporan Bulanan');
    $indexResponse->assertSee('rekap-juni.pdf');
    $indexResponse->assertSee('Bidang Upload Laporan');
    $indexResponse->assertSee('Lihat Rekap Aset');
    $indexResponse->assertSee(route('laporan-aset.index', ['mode' => 'aset']), false);
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

test('kepala dinas can switch laporan page to asset recap view', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REPORT-REKAP-KD-' . uniqid(),
        'nama_bidang' => 'Bidang Rekap Kepala Dinas',
        'nama_ruangan' => 'Ruang Rekap Kepala Dinas',
    ]);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);

    makeRegisterReportAsset([
        'kode_aset' => 'REG-KADIS-REKAP',
        'nama_aset' => 'Aset Rekap Kepala Dinas',
        'kode_barang' => 'Laptop',
        'bidang_id' => $bidang->id,
        'created_at' => Carbon::parse('2026-06-22 08:00:00'),
    ]);

    $response = $this->actingAs($kepalaDinas)
        ->get(route('laporan-aset.index', ['mode' => 'aset']));

    $response->assertOk();
    $response->assertSee('Daftar Rekap Aset');
    $response->assertSee('Aset Rekap Kepala Dinas');
    $response->assertSee('REG-KADIS-REKAP');
    $response->assertSee('Lihat Rekap Laporan');
    $response->assertSee(route('laporan-aset.index', ['mode' => 'laporan']), false);
    $response->assertSee('name="mode" value="aset"', false);
    $response->assertDontSee('Daftar Rekap Laporan');
    $response->assertDontSee(route('upload-laporan.index'), false);
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
