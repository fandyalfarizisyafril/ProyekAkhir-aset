<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\PenghapusanAset;
use App\Models\PenyusutanAset;
use App\Models\User;

test('super admin can view verified assets for deletion', function () {
    [$superAdmin, $admin, $bidang] = f17DeletionActors();
    f17RegisterAsset($bidang->id, $admin->id, 'F17-REG-VIEW-001', 'Laptop Siap Hapus', 'Terverifikasi');
    f17RegisterAsset($bidang->id, $admin->id, 'F17-REG-PENDING-001', 'Printer Belum Verifikasi', 'Perlu Verifikasi');
    f17SmkiAsset($bidang->id, $admin->id, 'F17-SMKI-VIEW-001', 'Aplikasi Siap Hapus', 'Terverifikasi');

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.penghapusan-aset.index'));

    $response->assertOk();
    $response->assertSee('Penghapusan Aset');
    $response->assertSee('Laptop Siap Hapus');
    $response->assertSee('Aplikasi Siap Hapus');
    $response->assertDontSee('Printer Belum Verifikasi');
    $response->assertDontSee('Riwayat Penghapusan Terbaru');
    $response->assertDontSee('Siap Dihapus');
    $response->assertDontSee('Riwayat Hapus');
    $response->assertDontSee('Register Dihapus');
});

test('super admin opens deletion history from dedicated history view', function () {
    [$superAdmin, $admin, $bidang] = f17DeletionActors();
    $asset = f17RegisterAsset($bidang->id, $admin->id, 'F17-REG-HISTORY-001', 'Aset Riwayat Hapus', 'Terverifikasi');

    PenghapusanAset::create([
        'aset_register_id' => $asset->id,
        'aset_smki_id' => null,
        'jenis_aset' => 'register',
        'kode_aset' => $asset->kode_aset,
        'nama_aset' => $asset->nama_aset,
        'bidang_id' => $bidang->id,
        'nilai_buku' => 750000,
        'tanggal_penghapusan' => now()->toDateString(),
        'metode_penghapusan' => 'Pemusnahan',
        'alasan' => 'Aset rusak berat dan dicatat sebagai riwayat.',
        'status_sebelum' => 'Tersedia',
        'dihapus_oleh' => $superAdmin->id,
    ]);

    $defaultResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.penghapusan-aset.index'));

    $defaultResponse->assertOk();
    $defaultResponse->assertSee('Daftar Aset Aktif');
    $defaultResponse->assertSee('Riwayat Penghapusan');
    $defaultResponse->assertDontSee('Riwayat Penghapusan Terbaru');

    $historyResponse = $this->actingAs($superAdmin)
        ->get(route('super-admin.penghapusan-aset.index', ['view' => 'riwayat']));

    $historyResponse->assertOk();
    $historyResponse->assertSee('Riwayat Penghapusan Terbaru');
    $historyResponse->assertSee('Aset Riwayat Hapus');
    $historyResponse->assertDontSee('Hanya aset terverifikasi dan belum dihapus yang tampil di daftar ini.');
});

test('super admin can delete register asset and store book value', function () {
    [$superAdmin, $admin, $bidang] = f17DeletionActors();
    $asset = f17RegisterAsset($bidang->id, $admin->id, 'F17-REG-DELETE-001', 'Server Rusak Berat', 'Terverifikasi', 10000000);

    PenyusutanAset::create([
        'aset_register_id' => $asset->id,
        'tahun' => 2026,
        'umur_manfaat_tahun' => 5,
        'nilai_awal_tahun' => 6000000,
        'nilai_residu' => 0,
        'beban_penyusutan' => 2000000,
        'nilai_akhir_tahun' => 4000000,
        'metode' => 'Garis Lurus',
    ]);

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.penghapusan-aset.store', ['register', $asset->id]), [
            'tanggal_penghapusan' => now()->toDateString(),
            'metode_penghapusan' => 'Pemusnahan',
            'alasan' => 'Rusak berat dan tidak ekonomis diperbaiki.',
            'jenis' => 'Semua Jenis',
            'bidang_id' => 'Semua Bidang',
        ]);

    $response->assertRedirect(route('super-admin.penghapusan-aset.index', [
        'jenis' => 'Semua Jenis',
        'bidang_id' => 'Semua Bidang',
        'search' => null,
    ]));

    expect($asset->fresh()->status)->toBe('Dihapus');
    expect($asset->fresh()->metode_pemusnahan)->toBe('Pemusnahan');

    $deletion = PenghapusanAset::where('aset_register_id', $asset->id)->first();
    expect($deletion)->not->toBeNull();
    expect($deletion->jenis_aset)->toBe('register');
    expect((float) $deletion->nilai_buku)->toBe(4000000.0);
    expect($deletion->dihapus_oleh)->toBe($superAdmin->id);
});

test('super admin can delete smki asset without book value', function () {
    [$superAdmin, $admin, $bidang] = f17DeletionActors();
    $asset = f17SmkiAsset($bidang->id, $admin->id, 'F17-SMKI-DELETE-001', 'Firewall SMKI', 'Terverifikasi');

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.penghapusan-aset.store', ['smki', $asset->id]), [
            'tanggal_penghapusan' => now()->toDateString(),
            'metode_penghapusan' => 'Pengalihan',
            'alasan' => 'Diganti dengan perangkat baru.',
            'jenis' => 'smki',
            'bidang_id' => $bidang->id,
        ]);

    $response->assertRedirect(route('super-admin.penghapusan-aset.index', [
        'jenis' => 'smki',
        'bidang_id' => (string) $bidang->id,
        'search' => null,
    ]));

    expect($asset->fresh()->status)->toBe('Dihapus');
    $this->assertDatabaseHas('penghapusan_aset', [
        'aset_smki_id' => $asset->id,
        'jenis_aset' => 'smki',
        'nilai_buku' => null,
        'metode_penghapusan' => 'Pengalihan',
    ]);
});

test('super admin cannot delete unverified asset', function () {
    [$superAdmin, $admin, $bidang] = f17DeletionActors();
    $asset = f17RegisterAsset($bidang->id, $admin->id, 'F17-REG-BLOCK-001', 'Aset Belum Verifikasi', 'Perlu Verifikasi');

    $response = $this->actingAs($superAdmin)
        ->post(route('super-admin.penghapusan-aset.store', ['register', $asset->id]), [
            'tanggal_penghapusan' => now()->toDateString(),
            'metode_penghapusan' => 'Pemusnahan',
            'alasan' => 'Percobaan penghapusan aset belum terverifikasi.',
            'jenis' => 'Semua Jenis',
            'bidang_id' => 'Semua Bidang',
        ]);

    $response->assertForbidden();
    expect($asset->fresh()->status)->toBe('Aktif');
    $this->assertDatabaseMissing('penghapusan_aset', [
        'aset_register_id' => $asset->id,
    ]);
});

function f17DeletionActors(): array
{
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $bidang = Bidang::create([
        'kode_bidang' => 'F17-' . uniqid(),
        'nama_bidang' => 'Bidang F17',
        'nama_ruangan' => 'Ruang F17',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    return [$superAdmin, $admin, $bidang];
}

function f17RegisterAsset(
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
        'kode_barang' => 'Kategori F17',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidangId,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang F17',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => $value,
        'kondisi' => 'Rusak Berat',
        'status' => 'Aktif',
        'status_verifikasi' => $verificationStatus,
        'dinput_oleh' => $userId,
    ]);
}

function f17SmkiAsset(
    int $bidangId,
    int $userId,
    string $code,
    string $name,
    string $verificationStatus,
): AsetSmki {
    return AsetSmki::create([
        'nomor_kode_barang' => $code,
        'jenis_barang' => $name,
        'merk_model' => 'Perangkat F17',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Rusak Berat',
        'bidang_id' => $bidangId,
        'ruangan' => 'Ruang F17',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Aktif',
        'status_verifikasi' => $verificationStatus,
        'dinput_oleh' => $userId,
    ]);
}
