<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\User;

test('super admin dashboard summarizes assets from all bidang', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();

    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-REG-001', 'Laptop', 'Baik', 10000000);
    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-REG-002', 'Printer', 'Rusak Berat', 2500000, 'Dipinjam');
    f18DashboardSmkiAsset($otherBidang->id, $admin->id, 'F18-SMKI-001', 'Aplikasi', 'Rusak Ringan');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Ringkasan Manajemen Aset');
    $response->assertSee('Bidang F18 Utama');
    $response->assertSee('Bidang F18 Lain');
    $response->assertViewHas('summary', function (array $summary) {
        return $summary['totalAssets'] === 3
            && $summary['goodCount'] === 1
            && $summary['damagedCount'] === 2
            && $summary['borrowedCount'] === 1
            && $summary['totalRegisterValue'] === 12500000.0;
    });
    $response->assertViewHas('bidangStats', function ($stats) {
        return $stats->contains(fn ($item) => $item['name'] === 'Bidang F18 Utama' && $item['count'] === 2)
            && $stats->contains(fn ($item) => $item['name'] === 'Bidang F18 Lain' && $item['count'] === 1);
    });
    $response->assertViewHas('userSummary', function (array $userSummary) {
        return $userSummary['totalUsers'] === 2
            && $userSummary['superAdminCount'] === 1
            && $userSummary['suspendedCount'] === 0;
    });
});

test('super admin dashboard shows user management summary', function () {
    [$bidang, , $superAdmin] = f18DashboardActors();

    User::factory()->create([
        'role' => 'Super Admin',
        'status' => 'Aktif',
    ]);
    User::factory()->create([
        'role' => 'Admin Perbidang',
        'status' => 'Ditangguhkan',
        'bidang_id' => $bidang->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Manajemen Pengguna');
    $response->assertSee('Total Pengguna');
    $response->assertSee('Super Admin');
    $response->assertSee('Ditangguhkan');
    $response->assertViewHas('userSummary', function (array $userSummary) {
        return $userSummary['totalUsers'] === 4
            && $userSummary['superAdminCount'] === 2
            && $userSummary['suspendedCount'] === 1;
    });
});

test('super admin dashboard can be filtered by bidang category and condition', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();

    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-FILTER-REG-001', 'Laptop', 'Baik');
    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-FILTER-REG-002', 'Laptop', 'Rusak Ringan');
    f18DashboardSmkiAsset($otherBidang->id, $admin->id, 'F18-FILTER-SMKI-001', 'Laptop', 'Baik');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard', [
            'bidang_id' => $bidang->id,
            'kategori' => 'Laptop',
            'kondisi' => 'Baik',
        ]));

    $response->assertOk();
    $response->assertViewHas('filters', function (array $filters) use ($bidang) {
        return (string) $filters['bidang_id'] === (string) $bidang->id
            && $filters['kategori'] === 'Laptop'
            && $filters['kondisi'] === 'Baik';
    });
    $response->assertViewHas('summary', fn (array $summary) => $summary['totalAssets'] === 1 && $summary['goodCount'] === 1);
    $response->assertViewHas('bidangStats', function ($stats) {
        return $stats->count() === 1
            && $stats->first()['name'] === 'Bidang F18 Utama'
            && $stats->first()['count'] === 1;
    });
});

function f18DashboardActors(): array
{
    $bidang = Bidang::create([
        'kode_bidang' => 'F18-' . uniqid(),
        'nama_bidang' => 'Bidang F18 Utama',
        'nama_ruangan' => 'Ruang F18 Utama',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'F18-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang F18 Lain',
        'nama_ruangan' => 'Ruang F18 Lain',
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    return [$bidang, $otherBidang, $superAdmin, $admin];
}

function f18DashboardRegisterAsset(
    int $bidangId,
    int $userId,
    string $code,
    string $category,
    string $condition,
    int $value = 1000000,
    string $status = 'Aktif',
): AsetRegister {
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => 'Aset Dashboard ' . $code,
        'kode_barang' => $category,
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => $condition,
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Dashboard',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => $value,
        'kondisi' => $condition,
        'status' => $status,
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}

function f18DashboardSmkiAsset(
    int $bidangId,
    int $userId,
    string $code,
    string $category,
    string $condition,
    string $status = 'Tersedia',
): AsetSmki {
    return AsetSmki::create([
        'nomor_kode_barang' => $code,
        'jenis_barang' => $category,
        'merk_model' => 'Aplikasi Dashboard',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => $condition,
        'bidang_id' => $bidangId,
        'ruangan' => 'Ruang Dashboard',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => $status,
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $userId,
    ]);
}
