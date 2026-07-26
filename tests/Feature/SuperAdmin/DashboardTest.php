<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\MutasiAset;
use App\Models\PeminjamanAset;
use App\Models\PenghapusanAset;
use App\Models\User;
use Illuminate\Support\Carbon;

test('super admin dashboard summarizes assets from all bidang', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();

    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-REG-001', 'Laptop', 'Baik', 10000000);
    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-REG-002', 'Printer', 'Rusak Berat', 2500000, 'Dipinjam');
    f18DashboardSmkiAsset($otherBidang->id, $admin->id, 'F18-SMKI-001', 'Aplikasi', 'Rusak Ringan');
    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-DELETED-REG-001', 'Meja', 'Baik', 750000, 'Dihapus');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Ringkasan Manajemen Aset');
    $response->assertSee('Menunggu Verifikasi Aset');
    $response->assertSee('Mutasi Menunggu Verifikasi');
    $response->assertSee('Peminjaman Menunggu Verifikasi');
    $response->assertSee(route('super-admin.kategori-aset.index'), false);
    $response->assertSee(route('super-admin.verifikasi-aset.index'), false);
    $response->assertSee(route('super-admin.verifikasi-mutasi.index'), false);
    $response->assertSee(route('super-admin.verifikasi-peminjaman.index'), false);
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

test('super admin dashboard excludes rejected assets from active inventory totals', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();

    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-VALID-REG-001', 'Laptop', 'Baik', 10000000);
    f18DashboardRegisterAsset(
        $bidang->id,
        $admin->id,
        'F18-REJECTED-REG-001',
        'Printer',
        'Baik',
        value: 2500000,
        status: 'Ditolak',
        verificationStatus: 'Ditolak',
    );
    f18DashboardSmkiAsset(
        $otherBidang->id,
        $admin->id,
        'F18-REJECTED-SMKI-001',
        'Aplikasi',
        'Baik',
        status: 'Ditolak',
        verificationStatus: 'Ditolak',
    );

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertViewHas('summary', function (array $summary) {
        return $summary['totalAssets'] === 1
            && $summary['registerCount'] === 1
            && $summary['smkiCount'] === 0
            && $summary['goodCount'] === 1
            && $summary['totalRegisterValue'] === 10000000.0;
    });
    $response->assertViewHas('bidangStats', function ($stats) {
        return $stats->count() === 1
            && $stats->first()['name'] === 'Bidang F18 Utama'
            && ! $stats->contains(fn ($item) => $item['name'] === 'Bidang F18 Lain');
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

test('super admin dashboard shows deletion summary card', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();
    $registerAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-DEL-REG-001', 'Laptop', 'Baik');
    $smkiAsset = f18DashboardSmkiAsset($otherBidang->id, $admin->id, 'F18-DEL-SMKI-001', 'Aplikasi', 'Baik');

    PenghapusanAset::create([
        'aset_register_id' => $registerAsset->id,
        'jenis_aset' => 'register',
        'kode_aset' => $registerAsset->kode_aset,
        'nama_aset' => $registerAsset->nama_aset,
        'bidang_id' => $bidang->id,
        'nilai_buku' => 1000000,
        'tanggal_penghapusan' => '2026-06-18',
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Ringkasan penghapusan dashboard.',
        'status_sebelum' => 'Tersedia',
        'dihapus_oleh' => $superAdmin->id,
    ]);
    PenghapusanAset::create([
        'aset_smki_id' => $smkiAsset->id,
        'jenis_aset' => 'smki',
        'kode_aset' => $smkiAsset->nomor_kode_barang,
        'nama_aset' => $smkiAsset->merk_model,
        'bidang_id' => $otherBidang->id,
        'nilai_buku' => null,
        'tanggal_penghapusan' => '2026-06-18',
        'metode_penghapusan' => 'Pengalihan',
        'alasan' => 'Ringkasan penghapusan dashboard.',
        'status_sebelum' => 'Tersedia',
        'dihapus_oleh' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Penghapusan Aset');
    $response->assertSee('Aset Nonaktif');
    $response->assertSee(route('super-admin.penghapusan-aset.index', ['view' => 'riwayat']), false);
    $response->assertViewHas('deletionSummary', function (array $summary) {
        return $summary['total'] === 2
            && $summary['registerCount'] === 1
            && $summary['smkiCount'] === 1;
    });
});

test('super admin dashboard shows assets waiting for verification', function () {
    [$bidang, , $superAdmin, $admin] = f18DashboardActors();
    $pendingRegister = f18DashboardRegisterAsset(
        $bidang->id,
        $admin->id,
        'F18-PENDING-REG-001',
        'Laptop Pending',
        'Baik',
        verificationStatus: 'Perlu Verifikasi'
    );
    $pendingRegister->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 9, 30),
        'updated_at' => Carbon::create(2026, 6, 18, 9, 30),
    ])->save();
    f18DashboardSmkiAsset(
        $bidang->id,
        $admin->id,
        'F18-PENDING-SMKI-001',
        'Aplikasi Pending',
        'Baik',
        verificationStatus: 'Perlu Verifikasi'
    );
    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-VERIFIED-REG-001', 'Laptop Verified', 'Baik');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Menunggu Verifikasi Aset');
    $response->assertSee('Aset Dashboard F18-PENDING-REG-001');
    $response->assertSee('Aplikasi Dashboard');
    $response->assertSee('18 Jun 2026 09:30');
    $response->assertSee('Belum diverifikasi');
    $response->assertViewHas('pendingVerificationAssets', function ($assets) {
        return $assets->count() === 2
            && $assets->contains(fn ($asset) => $asset->code === 'F18-PENDING-REG-001')
            && $assets->contains(fn ($asset) => $asset->code === 'F18-PENDING-SMKI-001')
            && ! $assets->contains(fn ($asset) => $asset->code === 'F18-VERIFIED-REG-001');
    });
});

test('super admin dashboard shows mutation requests waiting for verification', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();
    $asset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-MUTASI-REG-001', 'Laptop', 'Baik');

    $mutasi = MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidang->id,
        'bidang_tujuan_id' => $otherBidang->id,
        'alasan' => 'Dipakai sementara untuk kegiatan dashboard.',
        'status' => 'Menunggu Verifikasi',
        'diajukan_oleh' => $admin->id,
        'tanggal_mutasi' => '2026-06-18',
    ]);
    $mutasi->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 10, 15),
        'updated_at' => Carbon::create(2026, 6, 18, 10, 15),
    ])->save();

    $approvedAsset = f18DashboardSmkiAsset($bidang->id, $admin->id, 'F18-MUTASI-SMKI-OK', 'Aplikasi', 'Baik');
    MutasiAset::create([
        'jenis_aset' => 'smki',
        'aset_smki_id' => $approvedAsset->id,
        'bidang_asal_id' => $bidang->id,
        'bidang_tujuan_id' => $otherBidang->id,
        'alasan' => 'Sudah diproses.',
        'status' => 'Disetujui',
        'diajukan_oleh' => $admin->id,
        'disetujui_oleh' => $superAdmin->id,
        'tanggal_mutasi' => '2026-06-12',
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Mutasi Menunggu Verifikasi');
    $response->assertSee('Aset Dashboard F18-MUTASI-REG-001');
    $response->assertSee('Bidang F18 Utama');
    $response->assertSee('Bidang F18 Lain');
    $response->assertSee('18 Jun 2026 10:15');
    $response->assertSee('Diajukan');
    $response->assertViewHas('pendingMutationCount', 1);
    $response->assertViewHas('pendingMutationRequests', function ($mutations) use ($mutasi) {
        return $mutations->count() === 1
            && $mutations->first()->id === $mutasi->id
            && $mutations->first()->asset_code === 'F18-MUTASI-REG-001';
    });
});

test('super admin dashboard shows loan requests waiting for verification', function () {
    [$bidang, , $superAdmin, $admin] = f18DashboardActors();
    $asset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-PINJAM-REG-001', 'Laptop', 'Baik');

    $loan = PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $asset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Daud Markhesywan',
        'tanggal_pinjam' => '2026-06-18',
        'tanggal_rencana_kembali' => '2026-06-24',
        'keperluan' => 'Dipinjam untuk kegiatan dashboard.',
        'status' => 'Menunggu Verifikasi',
    ]);
    $loan->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 11, 20),
        'updated_at' => Carbon::create(2026, 6, 18, 11, 20),
    ])->save();

    $approvedAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-PINJAM-REG-OK', 'Laptop', 'Baik');
    PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $approvedAsset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Peminjam Selesai',
        'tanggal_pinjam' => '2026-06-12',
        'tanggal_rencana_kembali' => '2026-06-20',
        'keperluan' => 'Sudah diproses.',
        'status' => 'Disetujui',
        'disetujui_oleh' => $superAdmin->id,
    ]);

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Peminjaman Menunggu Verifikasi');
    $response->assertSee('Aset Dashboard F18-PINJAM-REG-001');
    $response->assertSee('Daud Markhesywan');
    $response->assertSee('18 Jun 2026 11:20');
    $response->assertSee('24 Jun 2026');
    $response->assertViewHas('pendingLoanCount', 1);
    $response->assertViewHas('pendingLoanRequests', function ($loans) use ($loan) {
        return $loans->count() === 1
            && $loans->first()->id === $loan->id
            && $loans->first()->asset_code === 'F18-PINJAM-REG-001';
    });
});

test('super admin dashboard shows recent activities', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();

    $verifiedAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-ACT-REG-001', 'Laptop', 'Baik');
    $verifiedAsset->forceFill([
        'status_verifikasi' => 'Terverifikasi',
        'diverifikasi_oleh' => $superAdmin->id,
        'updated_at' => Carbon::create(2026, 6, 18, 12, 30),
    ])->save();

    $mutatedAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-ACT-MUT-001', 'Laptop', 'Baik');
    $mutation = MutasiAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $mutatedAsset->id,
        'bidang_asal_id' => $bidang->id,
        'bidang_tujuan_id' => $otherBidang->id,
        'alasan' => 'Aktivitas mutasi dashboard.',
        'status' => 'Disetujui',
        'diajukan_oleh' => $admin->id,
        'disetujui_oleh' => $superAdmin->id,
        'tanggal_mutasi' => '2026-06-18',
    ]);
    $mutation->forceFill([
        'updated_at' => Carbon::create(2026, 6, 18, 12, 0),
    ])->save();

    $loanAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-ACT-PIN-001', 'Laptop', 'Baik');
    $loan = PeminjamanAset::create([
        'jenis_aset' => 'register',
        'aset_register_id' => $loanAsset->id,
        'bidang_asal_id' => $bidang->id,
        'peminjam_id' => $admin->id,
        'nama_peminjam' => 'Daud Markhesywan',
        'tanggal_pinjam' => '2026-06-18',
        'tanggal_rencana_kembali' => '2026-06-24',
        'keperluan' => 'Aktivitas peminjaman dashboard.',
        'status' => 'Ditolak',
        'disetujui_oleh' => $superAdmin->id,
    ]);
    $loan->forceFill([
        'updated_at' => Carbon::create(2026, 6, 18, 11, 30),
    ])->save();

    $deletion = PenghapusanAset::create([
        'aset_register_id' => $verifiedAsset->id,
        'jenis_aset' => 'register',
        'kode_aset' => 'F18-ACT-HAPUS-001',
        'nama_aset' => 'Aset Aktivitas Dihapus',
        'bidang_id' => $bidang->id,
        'nilai_buku' => 500000,
        'tanggal_penghapusan' => '2026-06-18',
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Aktivitas penghapusan dashboard.',
        'status_sebelum' => 'Aktif',
        'dihapus_oleh' => $superAdmin->id,
    ]);
    $deletion->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 11, 0),
        'updated_at' => Carbon::create(2026, 6, 18, 11, 0),
    ])->save();

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Riwayat Aktivitas Terbaru');
    $response->assertSee('Aset Register diverifikasi');
    $response->assertSee('Mutasi aset disetujui');
    $response->assertSee('Peminjaman aset ditolak');
    $response->assertViewHas('recentActivities', function ($activities) {
        return $activities->count() === 3
            && $activities->contains(fn ($activity) => $activity->title === 'Aset Register diverifikasi')
            && $activities->contains(fn ($activity) => $activity->title === 'Mutasi aset disetujui')
            && $activities->contains(fn ($activity) => $activity->title === 'Peminjaman aset ditolak');
    });
});

test('super admin dashboard shows priority issue assets', function () {
    [$bidang, , $superAdmin, $admin] = f18DashboardActors();

    $lightDamage = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-ISSUE-REG-001', 'Printer', 'Rusak Ringan');
    $lightDamage->forceFill([
        'updated_at' => Carbon::create(2026, 6, 18, 13, 0),
    ])->save();

    $heavyDamage = f18DashboardSmkiAsset($bidang->id, $admin->id, 'F18-ISSUE-SMKI-001', 'Aplikasi', 'Rusak Berat');
    $heavyDamage->forceFill([
        'updated_at' => Carbon::create(2026, 6, 18, 12, 0),
    ])->save();

    f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-ISSUE-REG-OK', 'Laptop', 'Baik');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Aset Bermasalah Prioritas');
    $response->assertSee('Aset Dashboard F18-ISSUE-REG-001');
    $response->assertSee('Aplikasi Dashboard');
    $response->assertSee('Rusak Berat');
    $response->assertSee('Rusak Ringan');
    $response->assertViewHas('priorityIssueAssets', function ($assets) {
        return $assets->count() === 2
            && $assets->first()->condition === 'Rusak Berat'
            && $assets->contains(fn ($asset) => $asset->code === 'F18-ISSUE-REG-001')
            && $assets->contains(fn ($asset) => $asset->code === 'F18-ISSUE-SMKI-001');
    });
});

test('super admin dashboard can be filtered by bidang year and condition', function () {
    [$bidang, $otherBidang, $superAdmin, $admin] = f18DashboardActors();

    $includedAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-FILTER-REG-001', 'Laptop', 'Baik');
    $includedAsset->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 9, 0),
        'updated_at' => Carbon::create(2026, 6, 18, 9, 0),
    ])->save();

    $differentConditionAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-FILTER-REG-002', 'Laptop', 'Rusak Ringan');
    $differentConditionAsset->forceFill([
        'created_at' => Carbon::create(2026, 6, 18, 10, 0),
        'updated_at' => Carbon::create(2026, 6, 18, 10, 0),
    ])->save();

    $differentYearAsset = f18DashboardRegisterAsset($bidang->id, $admin->id, 'F18-FILTER-REG-003', 'Laptop', 'Baik');
    $differentYearAsset->forceFill([
        'created_at' => Carbon::create(2025, 6, 18, 9, 0),
        'updated_at' => Carbon::create(2025, 6, 18, 9, 0),
    ])->save();

    f18DashboardSmkiAsset($otherBidang->id, $admin->id, 'F18-FILTER-SMKI-001', 'Laptop', 'Baik');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.dashboard', [
            'bidang_id' => $bidang->id,
            'tahun' => 2026,
            'kondisi' => 'Baik',
        ]));

    $response->assertOk();
    $response->assertViewHas('filters', function (array $filters) use ($bidang) {
        return (string) $filters['bidang_id'] === (string) $bidang->id
            && (string) $filters['tahun'] === '2026'
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

function f18DashboardSmkiAsset(
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
