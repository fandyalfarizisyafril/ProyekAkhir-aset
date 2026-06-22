<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\PeminjamanAset;
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
    $response->assertSee('Total Aset Bidang');
    $response->assertSee('Aset Menunggu Verifikasi');
    $response->assertSee('Mutasi Menunggu Verifikasi');
    $response->assertSee('Peminjaman Menunggu Verifikasi');
    $response->assertSee(route('admin-perbidang.mutasi-aset.index', ['status' => 'Menunggu Verifikasi']), false);
    $response->assertSee(route('admin-perbidang.peminjaman-aset.index', ['status' => 'Menunggu Verifikasi']), false);
    $response->assertDontSee('Aset Kondisi Baik');
    $response->assertDontSee('Rusak / Perbaikan');
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

test('admin perbidang dashboard excludes deleted assets from active inventory totals', function () {
    [$bidang, , $admin] = dashboardActors();

    dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-ACTIVE-REG-001', 'Laptop', 'Baik', 10000000);
    dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-DELETED-REG-001', 'Printer', 'Baik', 2500000, 'Dihapus');
    dashboardSmkiAsset($bidang->id, $admin->id, 'DASH-DELETED-SMKI-001', 'Aplikasi', 'Baik', 'Dihapus');

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertViewHas('summary', function (array $summary) {
        return $summary['totalAssets'] === 1
            && $summary['registerCount'] === 1
            && $summary['smkiCount'] === 0
            && $summary['totalRegisterValue'] === 10000000.0;
    });
    $response->assertViewHas('categoryStats', function ($stats) {
        return $stats->count() === 1
            && $stats->first()['name'] === 'Laptop';
    });
});

test('admin perbidang dashboard can be filtered by category year and condition', function () {
    [$bidang, , $admin] = dashboardActors();

    $currentYearAsset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-FILTER-REG-001', 'Laptop', 'Baik');
    $currentYearAsset->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 9, 0),
        'updated_at' => Carbon::create(2026, 6, 18, 9, 0),
    ])->save();
    $previousYearAsset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-FILTER-REG-002', 'Laptop', 'Baik');
    $previousYearAsset->forceFill([
        'created_at' => Carbon::create(2025, 6, 18, 9, 0),
        'updated_at' => Carbon::create(2025, 6, 18, 9, 0),
    ])->save();
    $otherCategoryAsset = dashboardSmkiAsset($bidang->id, $admin->id, 'DASH-FILTER-SMKI-001', 'Aplikasi', 'Baik');
    $otherCategoryAsset->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 9, 0),
        'updated_at' => Carbon::create(2026, 6, 18, 9, 0),
    ])->save();

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard', [
            'kategori' => 'Laptop',
            'tahun' => '2026',
            'kondisi' => 'Baik',
        ]));

    $response->assertOk();
    $response->assertSee('Semua Tahun');
    $response->assertViewHas('filters', fn (array $filters) => $filters['kategori'] === 'Laptop' && $filters['tahun'] === '2026' && $filters['kondisi'] === 'Baik');
    $response->assertViewHas('yearOptions', function ($years) {
        return $years->contains(2026) && $years->contains(2025);
    });
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
    $response->assertDontSee('Aset terbaru dari Bidang Dashboard yang masih menunggu verifikasi Super Admin.');
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
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertSee('Mutasi Menunggu Verifikasi');
    $response->assertSee('Aset Dashboard DASH-MUT-REG-001');
    $response->assertSee('Diajukan');
    $response->assertDontSee('25 Jun 2026');
    $response->assertViewHas('pendingMutationRequests', function ($requests) {
        return $requests->count() === 1
            && $requests->first()->asset_code === 'DASH-MUT-REG-001';
    });
});

test('admin perbidang dashboard shows pending loan requests from own bidang', function () {
    [$bidang, $otherBidang, $admin] = dashboardActors();
    $asset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-LOAN-REG-001', 'Laptop Pinjam', 'Baik');
    $approvedAsset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-LOAN-APPROVED-001', 'Printer Pinjam', 'Baik');
    $otherAdmin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $otherBidang->id,
    ]);
    $otherAsset = dashboardRegisterAsset($otherBidang->id, $otherAdmin->id, 'DASH-LOAN-OTHER-001', 'Server Pinjam', 'Baik');

    $pendingLoan = PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Rani Peminjam Dashboard',
        'tanggal_pinjam' => '2026-06-18',
        'tanggal_rencana_kembali' => '2026-06-26',
        'keperluan' => 'Dipinjam untuk kegiatan sosialisasi bidang.',
        'status' => 'Menunggu Verifikasi',
    ]);
    $pendingLoan->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 11, 45),
        'updated_at' => Carbon::create(2026, 6, 18, 11, 45),
    ])->save();

    PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $approvedAsset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Peminjam Disetujui',
        'tanggal_pinjam' => '2026-06-18',
        'tanggal_rencana_kembali' => '2026-06-24',
        'keperluan' => 'Sudah tidak perlu tampil di pending dashboard.',
        'status' => 'Disetujui',
    ]);

    PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $otherAsset->id,
        'bidang_asal_id' => $otherBidang->id,
        'peminjam_id' => $otherAdmin->id,
        'nama_peminjam' => 'Peminjam Bidang Lain',
        'tanggal_pinjam' => '2026-06-18',
        'tanggal_rencana_kembali' => '2026-06-27',
        'keperluan' => 'Tidak boleh muncul di dashboard bidang ini.',
        'status' => 'Menunggu Verifikasi',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertSee('Peminjaman Menunggu Verifikasi');
    $response->assertSee('Aset Dashboard DASH-LOAN-REG-001');
    $response->assertSee('Rani Peminjam Dashboard');
    $response->assertSee('18 Jun 2026 11:45');
    $response->assertSee('26 Jun 2026');
    $response->assertSee(route('admin-perbidang.peminjaman-aset.show', $pendingLoan->id), false);
    $response->assertDontSee('DASH-LOAN-OTHER-001');
    $response->assertViewHas('pendingLoanRequests', function ($requests) {
        return $requests->count() === 1
            && $requests->first()->asset_code === 'DASH-LOAN-REG-001'
            && $requests->first()->borrower_name === 'Rani Peminjam Dashboard'
            && ! $requests->contains(fn ($request) => $request->asset_code === 'DASH-LOAN-APPROVED-001');
    });
});

test('admin perbidang dashboard shows active borrowed assets from own bidang', function () {
    [$bidang, $otherBidang, $admin] = dashboardActors();
    $asset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-ACTIVE-LOAN-001', 'Laptop Aktif Pinjam', 'Baik', status: 'Dipinjam');
    $returnedAsset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-RETURNED-LOAN-001', 'Printer Kembali', 'Baik');
    $otherAdmin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $otherBidang->id,
    ]);
    $otherAsset = dashboardRegisterAsset($otherBidang->id, $otherAdmin->id, 'DASH-OTHER-ACTIVE-LOAN-001', 'Server Pinjam', 'Baik', status: 'Dipinjam');

    $activeLoan = PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Rani Peminjam Aktif',
        'tanggal_pinjam' => '2026-06-19',
        'tanggal_rencana_kembali' => '2026-06-24',
        'keperluan' => 'Dipinjam untuk kegiatan operasional aktif.',
        'status' => 'Disetujui',
        'disetujui_oleh' => $admin->id,
    ]);
    $activeLoan->forceFill([
        'created_at' => Carbon::create(2026, 6, 19, 8, 30),
        'updated_at' => Carbon::create(2026, 6, 19, 8, 45),
    ])->save();

    PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $returnedAsset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Peminjam Sudah Kembali',
        'tanggal_pinjam' => '2026-06-10',
        'tanggal_rencana_kembali' => '2026-06-15',
        'tanggal_kembali' => '2026-06-14',
        'keperluan' => 'Sudah dikembalikan sehingga tidak tampil sebagai aktif.',
        'status' => 'Dikembalikan',
        'disetujui_oleh' => $admin->id,
    ]);

    PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $otherAsset->id,
        'bidang_asal_id' => $otherBidang->id,
        'peminjam_id' => $otherAdmin->id,
        'nama_peminjam' => 'Peminjam Bidang Lain Aktif',
        'tanggal_pinjam' => '2026-06-19',
        'tanggal_rencana_kembali' => '2026-06-26',
        'keperluan' => 'Tidak boleh muncul di dashboard bidang ini.',
        'status' => 'Disetujui',
        'disetujui_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertSee('Aset Sedang Dipinjam');
    $response->assertSee('Aset Dashboard DASH-ACTIVE-LOAN-001');
    $response->assertSee('Rani Peminjam Aktif');
    $response->assertSee('19 Jun 2026 08:30');
    $response->assertSee('24 Jun 2026');
    $response->assertSee(route('admin-perbidang.peminjaman-aset.show', $activeLoan->id), false);
    $response->assertDontSee('DASH-OTHER-ACTIVE-LOAN-001');
    $response->assertViewHas('activeLoanRequests', function ($requests) {
        return $requests->count() === 1
            && $requests->first()->asset_code === 'DASH-ACTIVE-LOAN-001'
            && $requests->first()->borrower_name === 'Rani Peminjam Aktif'
            && ! $requests->contains(fn ($request) => $request->asset_code === 'DASH-RETURNED-LOAN-001');
    });
});

test('admin perbidang dashboard shows recent activities from own bidang', function () {
    [$bidang, $otherBidang, $admin] = dashboardActors();
    $superAdmin = User::factory()->create([
        'role' => 'Super Admin',
        'nama' => 'Super Admin Dashboard',
    ]);
    $verifiedAsset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-ACT-REG-001', 'Laptop Aktivitas', 'Baik');
    $mutationAsset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-ACT-MUT-001', 'Printer Aktivitas', 'Baik');
    $loanAsset = dashboardRegisterAsset($bidang->id, $admin->id, 'DASH-ACT-LOAN-001', 'Scanner Aktivitas', 'Baik');
    $otherAsset = dashboardRegisterAsset($otherBidang->id, $admin->id, 'DASH-ACT-OTHER-001', 'Server Aktivitas', 'Baik');

    $verifiedAsset->forceFill([
        'diverifikasi_oleh' => $superAdmin->id,
        'updated_at' => Carbon::create(2026, 6, 21, 9, 15),
    ])->save();

    $mutation = MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $mutationAsset->id,
        'bidang_asal_id' => $bidang->id,
        'bidang_tujuan_id' => $otherBidang->id,
        'alasan' => 'Dipakai sementara oleh bidang lain.',
        'status' => 'Disetujui',
        'diajukan_oleh' => $admin->id,
        'disetujui_oleh' => $superAdmin->id,
        'tanggal_mutasi' => '2026-06-21',
    ]);
    $mutation->forceFill([
        'created_at' => Carbon::create(2026, 6, 21, 9, 0),
        'updated_at' => Carbon::create(2026, 6, 21, 9, 20),
    ])->save();

    $loan = PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $loanAsset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Rani Aktivitas',
        'tanggal_pinjam' => '2026-06-21',
        'tanggal_rencana_kembali' => '2026-06-25',
        'keperluan' => 'Kebutuhan aktivitas dashboard.',
        'status' => 'Ditolak',
        'disetujui_oleh' => $superAdmin->id,
    ]);
    $loan->forceFill([
        'created_at' => Carbon::create(2026, 6, 21, 9, 5),
        'updated_at' => Carbon::create(2026, 6, 21, 9, 25),
    ])->save();

    $otherAsset->forceFill([
        'diverifikasi_oleh' => $superAdmin->id,
        'updated_at' => Carbon::create(2026, 6, 21, 9, 30),
    ])->save();

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.dashboard'));

    $response->assertOk();
    $response->assertSee('Riwayat Aktivitas Terbaru Bidang');
    $response->assertSee('Aset Register diverifikasi');
    $response->assertSee('Mutasi aset disetujui');
    $response->assertSee('Peminjaman aset ditolak');
    $response->assertSee('DASH-ACT-REG-001');
    $response->assertSee('DASH-ACT-MUT-001');
    $response->assertSee('DASH-ACT-LOAN-001');
    $response->assertSee('Super Admin Dashboard');
    $response->assertSee('21 Jun 2026 09:25');
    $response->assertSee(route('admin-perbidang.data-aset-register.edit', $verifiedAsset->id), false);
    $response->assertSee(route('admin-perbidang.mutasi-aset.show', $mutation->id), false);
    $response->assertSee(route('admin-perbidang.peminjaman-aset.show', $loan->id), false);
    $response->assertDontSee('DASH-ACT-OTHER-001');
    $response->assertViewHas('recentActivities', function ($activities) {
        return $activities->count() === 3
            && $activities->contains(fn ($activity) => $activity->title === 'Aset Register diverifikasi')
            && $activities->contains(fn ($activity) => $activity->title === 'Mutasi aset disetujui')
            && $activities->contains(fn ($activity) => $activity->title === 'Peminjaman aset ditolak')
            && ! $activities->contains(fn ($activity) => str_contains($activity->description, 'DASH-ACT-OTHER-001'));
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
