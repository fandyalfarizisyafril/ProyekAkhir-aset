<?php

use App\Models\AsetRegister;
use App\Models\Bidang;
use App\Models\PenyusutanAset;
use App\Models\User;
use Illuminate\Support\Carbon;

test('super admin can view verified register assets for depreciation', function () {
    [$superAdmin, $admin, $bidang] = f16DepreciationActors();
    f16RegisterAsset($bidang->id, $admin->id, 'F16-REG-001', 'Laptop Penyusutan', 'Terverifikasi');
    f16RegisterAsset($bidang->id, $admin->id, 'F16-REG-PENDING', 'Printer Pending', 'Perlu Verifikasi');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.penyusutan-aset.index'));

    $response->assertOk();
    $response->assertSee('Penyusutan Aset');
    $response->assertSee('Laptop Penyusutan');
    $response->assertDontSee('Printer Pending');
});

test('super admin can calculate straight line depreciation for one asset', function () {
    [$superAdmin, $admin, $bidang] = f16DepreciationActors();
    $asset = f16RegisterAsset($bidang->id, $admin->id, 'F16-CALC-001', 'Server Penyusutan', 'Terverifikasi', 10000000);
    $asset->forceFill([
        'tanggal_perolehan' => Carbon::create(2024, 1, 1),
        'created_at' => Carbon::create(2026, 1, 1),
        'updated_at' => Carbon::create(2026, 1, 1),
    ])->save();

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.penyusutan-aset.calculate', $asset->id), [
            'tahun' => 2026,
            'umur_manfaat_mode' => 'manual',
            'umur_manfaat_tahun' => 5,
            'nilai_residu' => 0,
            'bidang_id' => 'Semua Bidang',
            'kategori' => 'Semua Kategori',
        ]);

    $response->assertRedirect(route('super-admin.penyusutan-aset.index', [
        'tahun' => 2026,
        'bidang_id' => 'Semua Bidang',
        'kategori' => 'Semua Kategori',
        'status_penyusutan' => 'Semua Status',
        'search' => null,
    ]));

    $depreciation = PenyusutanAset::where('aset_register_id', $asset->id)->where('tahun', 2026)->first();
    expect($depreciation)->not->toBeNull();
    expect((float) $depreciation->nilai_awal_tahun)->toBe(6000000.0);
    expect((float) $depreciation->beban_penyusutan)->toBe(2000000.0);
    expect((float) $depreciation->nilai_akhir_tahun)->toBe(4000000.0);
    expect($depreciation->metode)->toBe('Garis Lurus');
    expect($depreciation->dihitung_oleh)->toBe($superAdmin->id);
    expect($depreciation->tanggal_hitung)->not->toBeNull();

    $indexResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.penyusutan-aset.index', [
            'tahun' => 2026,
            'bidang_id' => 'Semua Bidang',
            'kategori' => 'Semua Kategori',
        ]));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Tahun ke-3');
    $indexResponse->assertSee('Lihat Jadwal Penyusutan', false);

    $scheduleResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.penyusutan-aset.schedule', [
            'aset_register' => $asset->id,
            'tahun' => 2026,
            'bidang_id' => 'Semua Bidang',
            'kategori' => 'Semua Kategori',
        ]));

    $scheduleResponse->assertOk();
    $scheduleResponse->assertSee('Jadwal Penyusutan Aset');
    $scheduleResponse->assertSee('Mulai Penyusutan');
    $scheduleResponse->assertSee('Tahun ke-1');
    $scheduleResponse->assertSee('Tahun ke-5');
    $scheduleResponse->assertSee('Rp 8.000.000');
    $scheduleResponse->assertSee('Rp 4.000.000');
    $scheduleResponse->assertSee('Rp 0');
    $scheduleResponse->assertSee('Akhir umur manfaat');
    expect(PenyusutanAset::where('aset_register_id', $asset->id)->count())->toBe(1);
});

test('super admin does not depreciate asset before acquisition year', function () {
    [$superAdmin, $admin, $bidang] = f16DepreciationActors();
    $asset = f16RegisterAsset($bidang->id, $admin->id, 'F16-BEFORE-001', 'Router Belum Perolehan', 'Terverifikasi', 4000000);
    $asset->forceFill([
        'tanggal_perolehan' => Carbon::create(2026, 7, 1),
        'created_at' => Carbon::create(2024, 1, 1),
        'updated_at' => Carbon::create(2024, 1, 1),
    ])->save();

    $this->actingAs($superAdmin)
        ->post(route('super-admin.penyusutan-aset.calculate', $asset->id), [
            'tahun' => 2025,
            'umur_manfaat_mode' => 'manual',
            'umur_manfaat_tahun' => 4,
            'nilai_residu' => 0,
            'bidang_id' => 'Semua Bidang',
            'kategori' => 'Semua Kategori',
        ])
        ->assertRedirect();

    $depreciation = PenyusutanAset::where('aset_register_id', $asset->id)->where('tahun', 2025)->first();
    expect((float) $depreciation->nilai_awal_tahun)->toBe(4000000.0);
    expect((float) $depreciation->beban_penyusutan)->toBe(0.0);
    expect((float) $depreciation->nilai_akhir_tahun)->toBe(4000000.0);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.penyusutan-aset.index', [
            'tahun' => 2025,
            'bidang_id' => 'Semua Bidang',
            'kategori' => 'Semua Kategori',
        ]));

    $response->assertOk();
    $response->assertSee('Sebelum tahun perolehan');
});

test('super admin can calculate depreciation for filtered assets only', function () {
    [$superAdmin, $admin, $bidang] = f16DepreciationActors();
    $otherBidang = Bidang::create([
        'kode_bidang' => 'F16-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang F16 Lain',
        'nama_ruangan' => 'Ruang F16 Lain',
    ]);
    $asset = f16RegisterAsset($bidang->id, $admin->id, 'F16-BULK-001', 'Laptop Bidang Filter', 'Terverifikasi', 5000000);
    $otherAsset = f16RegisterAsset($otherBidang->id, $admin->id, 'F16-BULK-002', 'Laptop Bidang Lain', 'Terverifikasi', 5000000);

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.penyusutan-aset.calculate-all'), [
            'tahun' => 2026,
            'umur_manfaat_mode' => 'manual',
            'umur_manfaat_tahun' => 5,
            'nilai_residu' => 0,
            'bidang_id' => $bidang->id,
            'kategori' => 'Semua Kategori',
        ]);

    $response->assertRedirect(route('super-admin.penyusutan-aset.index', [
        'tahun' => 2026,
        'bidang_id' => (string) $bidang->id,
        'kategori' => 'Semua Kategori',
        'status_penyusutan' => 'Semua Status',
        'search' => null,
    ]));
    expect(PenyusutanAset::where('aset_register_id', $asset->id)->exists())->toBeTrue();
    expect(PenyusutanAset::where('aset_register_id', $otherAsset->id)->exists())->toBeFalse();
});

test('super admin can calculate depreciation using category presets and category filter', function () {
    [$superAdmin, $admin, $bidang] = f16DepreciationActors();
    $laptop = f16RegisterAsset($bidang->id, $admin->id, 'F16-PRESET-001', 'Laptop Preset', 'Terverifikasi', 8000000, 'Laptop');
    $chair = f16RegisterAsset($bidang->id, $admin->id, 'F16-PRESET-002', 'Kursi Preset', 'Terverifikasi', 5000000, 'MEBEL-KURSI');

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.penyusutan-aset.calculate-all'), [
            'tahun' => 2026,
            'umur_manfaat_mode' => 'preset',
            'umur_manfaat_tahun' => 5,
            'nilai_residu' => 0,
            'bidang_id' => 'Semua Bidang',
            'kategori' => 'Laptop',
        ]);

    $response->assertRedirect(route('super-admin.penyusutan-aset.index', [
        'tahun' => 2026,
        'bidang_id' => 'Semua Bidang',
        'kategori' => 'Laptop',
        'status_penyusutan' => 'Semua Status',
        'search' => null,
    ]));

    $depreciation = PenyusutanAset::where('aset_register_id', $laptop->id)->where('tahun', 2026)->first();
    expect($depreciation)->not->toBeNull();
    expect($depreciation->umur_manfaat_tahun)->toBe(4);
    expect(PenyusutanAset::where('aset_register_id', $chair->id)->exists())->toBeFalse();
});

test('super admin can filter assets by depreciation status for selected year', function () {
    [$superAdmin, $admin, $bidang] = f16DepreciationActors();
    $calculatedAsset = f16RegisterAsset($bidang->id, $admin->id, 'F16-STATUS-001', 'Aset Sudah Hitung', 'Terverifikasi', 5000000);
    $uncalculatedAsset = f16RegisterAsset($bidang->id, $admin->id, 'F16-STATUS-002', 'Aset Belum Hitung', 'Terverifikasi', 5000000);

    PenyusutanAset::create([
        'aset_register_id' => $calculatedAsset->id,
        'tahun' => 2026,
        'umur_manfaat_tahun' => 5,
        'nilai_awal_tahun' => 5000000,
        'nilai_residu' => 0,
        'beban_penyusutan' => 1000000,
        'nilai_akhir_tahun' => 4000000,
        'metode' => 'Garis Lurus',
        'dihitung_oleh' => $superAdmin->id,
        'tanggal_hitung' => now(),
    ]);

    $calculatedResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.penyusutan-aset.index', [
            'tahun' => 2026,
            'status_penyusutan' => 'Sudah Dihitung',
        ]));

    $calculatedResponse->assertOk();
    $calculatedResponse->assertSee('Aset Sudah Hitung');
    $calculatedResponse->assertDontSee('Aset Belum Hitung');

    $uncalculatedResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.penyusutan-aset.index', [
            'tahun' => 2026,
            'status_penyusutan' => 'Belum Dihitung',
        ]));

    $uncalculatedResponse->assertOk();
    $uncalculatedResponse->assertSee('Aset Belum Hitung');
    $uncalculatedResponse->assertDontSee('Aset Sudah Hitung');
});

function f16DepreciationActors(): array
{
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $bidang = Bidang::create([
        'kode_bidang' => 'F16-' . uniqid(),
        'nama_bidang' => 'Bidang F16',
        'nama_ruangan' => 'Ruang F16',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    return [$superAdmin, $admin, $bidang];
}

function f16RegisterAsset(
    int $bidangId,
    int $userId,
    string $code,
    string $name,
    string $verificationStatus,
    int $value = 1000000,
    string $category = 'Kategori F16',
): AsetRegister {
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => $name,
        'kode_barang' => $category,
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang F16',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => $value,
        'tanggal_perolehan' => now()->toDateString(),
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => $verificationStatus,
        'dinput_oleh' => $userId,
    ]);
}
