<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\User;

test('admin perbidang dashboard summarizes assets from own bidang only', function () {
    [$bidang, $otherBidang, $admin] = dashboardActors();

    dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-REG-001', 'Laptop', 'Baik', 10000000);
    dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-REG-002', 'Printer', 'Rusak Berat', 2500000, 'Dipinjam');
    dashboardSmkiAsset($bidang->id, $admin->id, 'DASH-SMKI-001', 'Aplikasi', 'Rusak Ringan');
    dashboardRegisterAsset($otherBidang->id, $admin->id, 'DASH-OTHER-001', 'Server', 'Baik', 50000000);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertSee('Dashboard Admin Perbidang');
    $response->assertSee('Bidang Dashboard');
    $response->assertViewHas('summary', function (array $summary) {
        return $summary['totalAssets'] === 3
            && $summary['goodCount'] === 1
            && $summary['damagedCount'] === 2
            && $summary['borrowedCount'] === 1
            && $summary['totalRegisterValue'] === 12500000.0;
    });
    $response->assertViewHas('categoryStats', function ($stats) {
        return $stats->contains(fn ($item) => $item['name'] === 'Laptop' && $item['count'] === 1)
            && $stats->contains(fn ($item) => $item['name'] === 'Printer' && $item['count'] === 1)
            && $stats->contains(fn ($item) => $item['name'] === 'Aplikasi' && $item['count'] === 1)
            && ! $stats->contains(fn ($item) => $item['name'] === 'Server');
    });
});

test('admin perbidang dashboard can be filtered by category and condition', function () {
    [$bidang, , $admin] = dashboardActors();

    dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-FILTER-REG-001', 'Laptop', 'Baik');
    dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-FILTER-REG-002', 'Laptop', 'Rusak Ringan');
    dashboardSmkiAsset($bidang->id, $admin->id, 'DASH-FILTER-SMKI-001', 'Aplikasi', 'Baik');

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard', [
            'kategori' => 'Laptop',
            'kondisi' => 'Baik',
        ]));

    $response->assertOk();
    $response->assertViewHas('filters', fn (array $filters) => $filters['kategori'] === 'Laptop' && $filters['kondisi'] === 'Baik');
    $response->assertViewHas('summary', fn (array $summary) => $summary['totalAssets'] === 1 && $summary['goodCount'] === 1);
    $response->assertViewHas('categoryStats', function ($stats) {
        return $stats->count() === 1
            && $stats->first()['name'] === 'Laptop'
            && $stats->first()['count'] === 1;
    });
});

function dashboardActors(): array
{
    $bidang = Bidang::create([
        'kode_bidang' => 'DASH-' . uniqid(),
        'nama_bidang' => 'Bidang Dashboard',
        'nama_ruangan' => 'Ruang Dashboard',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'DASH-OTHER-' . uniqid(),
        'nama_bidang' => 'Bidang Lain',
        'nama_ruangan' => 'Ruang Lain',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    return [$bidang, $otherBidang, $admin];
}

function dashboardRegisterAsset(
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

function dashboardSmkiAsset(
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
