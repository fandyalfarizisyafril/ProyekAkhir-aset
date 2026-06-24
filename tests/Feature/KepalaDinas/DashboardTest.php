<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PenghapusanAset;
use App\Models\PenyusutanAset;
use App\Models\User;
use Illuminate\Support\Carbon;

test('kepala dinas dashboard summarizes verified assets from all bidang', function () {
    [$utama, $lain, $kepalaDinas, $admin] = f20DashboardActors();

    $router = f20RegisterAsset($utama->id, $admin->id, 'F20-REG-001', 'Router', 'Baik', 10000000);
    f20RegisterAsset($utama->id, $admin->id, 'F20-REG-002', 'Printer', 'Rusak Ringan', 5000000);
    f20SmkiAsset($lain->id, $admin->id, 'F20-SMKI-001', 'Aplikasi', 'Baik');
    f20RegisterAsset($lain->id, $admin->id, 'F20-PENDING-001', 'Laptop', 'Baik', 3000000, 'Aktif', 'Perlu Verifikasi');
    f20RegisterAsset($lain->id, $admin->id, 'F20-DELETED-001', 'Laptop', 'Baik', 2000000, 'Dihapus');

    PenyusutanAset::create([
        'aset_register_id' => $router->id,
        'tahun' => 2026,
        'umur_manfaat_tahun' => 5,
        'nilai_awal_tahun' => 10000000,
        'nilai_residu' => 0,
        'beban_penyusutan' => 2000000,
        'nilai_akhir_tahun' => 8000000,
        'metode' => 'Garis Lurus',
    ]);
    PenghapusanAset::create([
        'aset_register_id' => null,
        'aset_smki_id' => null,
        'jenis_aset' => 'register',
        'kode_aset' => 'F20-OLD-DEL',
        'nama_aset' => 'Aset Lama Dihapus',
        'bidang_id' => $utama->id,
        'nilai_buku' => 1000000,
        'tanggal_penghapusan' => '2026-06-20',
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Tidak ekonomis diperbaiki.',
        'status_sebelum' => 'Aktif',
        'dihapus_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard', ['tahun' => 2026]));

    $response->assertOk();
    $response->assertSee('Dashboard Pimpinan');
    $response->assertSee('Rp 15.000.000');
    $response->assertSee('Rp 2.000.000');
    $response->assertSee('Rp 13.000.000');
    $response->assertSee('Aset Register Bernilai Tertinggi');
    $response->assertSee(route('kepala-dinas.monitoring-aset.nonaktif'), false);
    $response->assertSee('Router F20-REG-001');
    $response->assertSee($utama->nama_bidang);
    $response->assertSee($lain->nama_bidang);
    $response->assertDontSee('F20-PENDING-001');
    $response->assertDontSee('F20-DELETED-001');
    $response->assertViewHas('summary', function (array $summary) {
        return $summary['totalAssets'] === 3
            && $summary['registerCount'] === 2
            && $summary['smkiCount'] === 1
            && $summary['goodCount'] === 2
            && $summary['damagedCount'] === 1
            && $summary['totalRegisterValue'] === 15000000.0
            && $summary['depreciationExpense'] === 2000000.0
            && $summary['bookValue'] === 13000000.0
            && $summary['deletedCount'] === 1;
    });
});

test('kepala dinas dashboard can be filtered by bidang category and condition', function () {
    [$utama, $lain, $kepalaDinas, $admin] = f20DashboardActors();

    f20RegisterAsset($utama->id, $admin->id, 'F20-FILTER-001', 'Laptop', 'Baik', 7000000);
    f20RegisterAsset($utama->id, $admin->id, 'F20-FILTER-002', 'Laptop', 'Rusak Ringan', 4000000);
    f20SmkiAsset($lain->id, $admin->id, 'F20-FILTER-SMKI', 'Laptop', 'Baik');

    $response = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard', [
            'tahun' => 2026,
            'bidang_id' => $utama->id,
            'kategori' => 'Laptop',
            'kondisi' => 'Baik',
        ]));

    $response->assertOk();
    $response->assertSee('F20-FILTER-001');
    $response->assertDontSee('F20-FILTER-002');
    $response->assertDontSee('F20-FILTER-SMKI');
    $response->assertViewHas('filters', function (array $filters) use ($utama) {
        return (string) $filters['bidang_id'] === (string) $utama->id
            && $filters['kategori'] === 'Laptop'
            && $filters['kondisi'] === 'Baik'
            && $filters['tahun'] === 2026;
    });
    $response->assertViewHas('summary', fn (array $summary) => $summary['totalAssets'] === 1 && $summary['totalRegisterValue'] === 7000000.0);
});

test('kepala dinas dashboard year filter limits all asset summaries consistently', function () {
    [$utama, , $kepalaDinas, $admin] = f20DashboardActors();

    $includedRegister = f20RegisterAsset($utama->id, $admin->id, 'F20-YEAR-2026', 'Laptop', 'Baik', 6000000);
    $includedRegister->forceFill([
        'created_at' => Carbon::create(2026, 6, 20, 9, 0),
        'updated_at' => Carbon::create(2026, 6, 20, 9, 0),
    ])->save();

    $excludedRegister = f20RegisterAsset($utama->id, $admin->id, 'F20-YEAR-2025', 'Laptop', 'Rusak Ringan', 4000000);
    $excludedRegister->forceFill([
        'created_at' => Carbon::create(2025, 6, 20, 9, 0),
        'updated_at' => Carbon::create(2025, 6, 20, 9, 0),
    ])->save();

    $includedSmki = f20SmkiAsset($utama->id, $admin->id, 'F20-YEAR-SMKI-2026', 'Aplikasi', 'Baik');
    $includedSmki->forceFill([
        'created_at' => Carbon::create(2026, 6, 21, 9, 0),
        'updated_at' => Carbon::create(2026, 6, 21, 9, 0),
    ])->save();

    $excludedSmki = f20SmkiAsset($utama->id, $admin->id, 'F20-YEAR-SMKI-2025', 'Aplikasi', 'Baik');
    $excludedSmki->forceFill([
        'created_at' => Carbon::create(2025, 6, 21, 9, 0),
        'updated_at' => Carbon::create(2025, 6, 21, 9, 0),
    ])->save();

    PenghapusanAset::create([
        'jenis_aset' => 'register',
        'kode_aset' => 'F20-YEAR-DEL-2026',
        'nama_aset' => 'Aset Dihapus Tahun 2026',
        'bidang_id' => $utama->id,
        'nilai_buku' => 1000000,
        'tanggal_penghapusan' => '2026-06-22',
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Tidak ekonomis diperbaiki.',
        'status_sebelum' => 'Aktif',
        'dihapus_oleh' => $admin->id,
    ]);
    PenghapusanAset::create([
        'jenis_aset' => 'register',
        'kode_aset' => 'F20-YEAR-DEL-2025',
        'nama_aset' => 'Aset Dihapus Tahun 2025',
        'bidang_id' => $utama->id,
        'nilai_buku' => 1000000,
        'tanggal_penghapusan' => '2025-06-22',
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Tidak ekonomis diperbaiki.',
        'status_sebelum' => 'Aktif',
        'dihapus_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard', ['tahun' => 2026]));

    $response->assertOk();
    $response->assertSee('F20-YEAR-2026');
    $response->assertDontSee('F20-YEAR-2025');
    $response->assertViewHas('summary', function (array $summary) {
        return $summary['totalAssets'] === 2
            && $summary['registerCount'] === 1
            && $summary['smkiCount'] === 1
            && $summary['goodCount'] === 2
            && $summary['damagedCount'] === 0
            && $summary['totalRegisterValue'] === 6000000.0
            && $summary['deletedCount'] === 1;
    });
});

function f20DashboardActors(): array
{
    $utama = Bidang::create([
        'kode_bidang' => 'F20-' . uniqid(),
        'nama_bidang' => 'Bidang F20 Utama',
        'nama_ruangan' => 'Ruang F20 Utama',
    ]);
    $lain = Bidang::create([
        'kode_bidang' => 'F20-LAIN-' . uniqid(),
        'nama_bidang' => 'Bidang F20 Lain',
        'nama_ruangan' => 'Ruang F20 Lain',
    ]);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $utama->id,
    ]);

    return [$utama, $lain, $kepalaDinas, $admin];
}

function f20RegisterAsset(
    int $bidangId,
    int $userId,
    string $code,
    string $category,
    string $condition,
    int $value,
    string $status = 'Aktif',
    string $verificationStatus = 'Terverifikasi',
): AsetRegister {
    return AsetRegister::create([
        'kode_aset' => $code,
        'nama_aset' => 'Router ' . $code,
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

function f20SmkiAsset(
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
        'merk_model' => 'Aplikasi ' . $code,
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
