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
    $asset->forceFill(['created_at' => Carbon::create(2024, 1, 1), 'updated_at' => Carbon::create(2024, 1, 1)])->save();

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.penyusutan-aset.calculate', $asset->id), [
            'tahun' => 2026,
            'umur_manfaat_tahun' => 5,
            'nilai_residu' => 0,
            'bidang_id' => 'Semua Bidang',
        ]);

    $response->assertRedirect(route('super-admin.penyusutan-aset.index', [
        'tahun' => 2026,
        'bidang_id' => 'Semua Bidang',
        'search' => null,
    ]));

    $depreciation = PenyusutanAset::where('aset_register_id', $asset->id)->where('tahun', 2026)->first();
    expect($depreciation)->not->toBeNull();
    expect((float) $depreciation->nilai_awal_tahun)->toBe(6000000.0);
    expect((float) $depreciation->beban_penyusutan)->toBe(2000000.0);
    expect((float) $depreciation->nilai_akhir_tahun)->toBe(4000000.0);
    expect($depreciation->metode)->toBe('Garis Lurus');
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
            'umur_manfaat_tahun' => 5,
            'nilai_residu' => 0,
            'bidang_id' => $bidang->id,
        ]);

    $response->assertRedirect(route('super-admin.penyusutan-aset.index', [
        'tahun' => 2026,
        'bidang_id' => (string) $bidang->id,
        'search' => null,
    ]));
    expect(PenyusutanAset::where('aset_register_id', $asset->id)->exists())->toBeTrue();
    expect(PenyusutanAset::where('aset_register_id', $otherAsset->id)->exists())->toBeFalse();
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
): AsetRegister {
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => $name,
        'kode_barang' => 'Kategori F16',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang F16',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => $value,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => $verificationStatus,
        'dinput_oleh' => $userId,
    ]);
}
