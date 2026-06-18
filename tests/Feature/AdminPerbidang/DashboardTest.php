<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\User;
use Illuminate\Support\Carbon;

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

test('admin perbidang dashboard shows pending input assets from own bidang', function () {
    [$bidang, $otherBidang, $admin] = dashboardActors();
    $pendingRegister = dashboardRegisterAsset(
        $bidang->id,
        $admin->id,
        'DASH-RECENT-REG-001',
        'Laptop Recent',
        'Baik',
        verificationStatus: 'Perlu Verifikasi'
    );
    $pendingRegister->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 10, 15),
        'updated_at' => Carbon::create(2026, 6, 18, 10, 15),
    ])->save();
    dashboardSmkiAsset(
        $bidang->id,
        $admin->id,
        'DASH-RECENT-SMKI-001',
        'Aplikasi Recent',
        'Baik',
        verificationStatus: 'Terverifikasi'
    );
    dashboardRegisterAsset($otherBidang->id, $admin->id, 'DASH-RECENT-OTHER-001', 'Server Lain', 'Baik');

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertSee('Aset Menunggu Verifikasi');
    $response->assertSee('Aset Dashboard DASH-RECENT-REG-001');
    $response->assertSee('18 Jun 2026 10:15');
    $response->assertSee('Perlu Verifikasi');
    $response->assertDontSee('DASH-RECENT-SMKI-001');
    $response->assertViewHas('recentInputAssets', function ($assets) {
        return $assets->count() === 1
            && $assets->contains(fn ($asset) => $asset->code === 'DASH-RECENT-REG-001')
            && ! $assets->contains(fn ($asset) => $asset->code === 'DASH-RECENT-SMKI-001')
            && ! $assets->contains(fn ($asset) => $asset->code === 'DASH-RECENT-OTHER-001');
    });
});

test('admin perbidang dashboard hides pending input card when all assets are verified', function () {
    [$bidang, , $admin] = dashboardActors();

    dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-HIDDEN-REG-001', 'Laptop Hidden', 'Baik');
    dashboardSmkiAsset($bidang->id, $admin->id, 'DASH-HIDDEN-SMKI-001', 'Aplikasi Hidden', 'Baik');

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Aset Menunggu Verifikasi');
    $response->assertViewHas('recentInputAssets', fn ($assets) => $assets->isEmpty());
});

test('admin perbidang dashboard shows pending mutation requests', function () {
    [$bidang, $otherBidang, $admin] = dashboardActors();
    $asset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-MUT-REG-001', 'Laptop Mutasi', 'Baik');

    MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidang->id,
        'bidang_tujuan_id' => $otherBidang->id,
        'alasan' => 'Dipakai sementara oleh bidang tujuan.',
        'status' => 'Menunggu Verifikasi',
        'diajukan_oleh' => $admin->id,
        'tanggal_mutasi' => '2026-06-18',
        'tanggal_rencana_pengembalian' => '2026-06-25',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertSee('Mutasi Menunggu Verifikasi');
    $response->assertSee('Aset Dashboard DASH-MUT-REG-001');
    $response->assertSee('25 Jun 2026');
    $response->assertViewHas('pendingMutationRequests', function ($requests) {
        return $requests->count() === 1
            && $requests->first()->asset_code === 'DASH-MUT-REG-001';
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
    string $verificationStatus = 'Terverifikasi',
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
        'status_verifikasi' => $verificationStatus,
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
    string $verificationStatus = 'Terverifikasi',
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
        'status_verifikasi' => $verificationStatus,
        'dinput_oleh' => $userId,
    ]);
}
