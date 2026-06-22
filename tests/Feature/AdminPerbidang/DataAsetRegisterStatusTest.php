<?php

use App\Models\AsetRegister;
use App\Models\AsetSmki;
use App\Models\Bidang;
use App\Models\KategoriAset;
use App\Models\User;

test('new register asset is shown as pending verification on admin perbidang list', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-STATUS-' . uniqid(),
        'nama_bidang' => 'Bidang Register Status',
        'nama_ruangan' => 'Ruang Register Status',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.data-aset-register.store'), [
            'kode_aset' => 'REG-STATUS-001',
            'nama_aset' => 'Laptop Status Verifikasi',
            'kode_barang' => 'KB-REG-STATUS',
            'kode_urut_barang' => '001',
            'status_barang' => 'Baik',
            'pemilik_aset' => 'Diskominfotik Riau',
            'pengguna' => 'Admin Bidang',
            'lokasi_aset' => 'Ruang Register Status',
            'metode_pemusnahan' => null,
            'kerahasiaan' => 'Umum',
            'kritikalitas' => 'SEDANG',
            'nilai' => 10000000,
            'keterangan' => 'Aset register baru untuk verifikasi.',
        ]);

    $response->assertRedirect(route('admin-perbidang.data-aset-register.index'));

    $asset = AsetRegister::first();
    expect($asset->status)->toBe('Tersedia');
    expect($asset->status_verifikasi)->toBe('Perlu Verifikasi');
    expect(KategoriAset::where('tipe', 'Register')->where('nama_kategori', 'KB-REG-STATUS')->exists())->toBeFalse();

    $indexResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Laptop Status Verifikasi');
    $indexResponse->assertSee('Perlu Verifikasi');
    $indexResponse->assertSee('Tersedia');
    $indexResponse->assertDontSee('MAINTENANCE');
});

test('register asset status filter uses verification status', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-FILTER-' . uniqid(),
        'nama_bidang' => 'Bidang Register Filter',
        'nama_ruangan' => 'Ruang Register Filter',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-FILTER-PENDING',
        'nama_aset' => 'Aset Register Pending',
        'kode_barang' => 'KB-PENDING',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Filter',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-FILTER-VERIFIED',
        'nama_aset' => 'Aset Register Terverifikasi',
        'kode_barang' => 'KB-VERIFIED',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Filter',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index', ['status' => 'Perlu Verifikasi']));

    $response->assertOk();
    $response->assertSee('Aset Register Pending');
    $response->assertDontSee('Aset Register Terverifikasi');
});

test('register asset list can be filtered by category', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-CATEGORY-' . uniqid(),
        'nama_bidang' => 'Bidang Register Kategori',
        'nama_ruangan' => 'Ruang Register Kategori',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-CATEGORY-LAPTOP',
        'nama_aset' => 'Laptop Filter Register',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Kategori',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-CATEGORY-PRINTER',
        'nama_aset' => 'Printer Filter Register',
        'kode_barang' => 'Printer',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Kategori',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 5000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index', ['kategori' => 'Laptop']));

    $response->assertOk();
    $response->assertSee('Semua Kategori');
    $response->assertSee('Laptop Filter Register');
    $response->assertDontSee('Printer Filter Register');
});

test('admin perbidang asset lists hide deleted assets from active inventory', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'ACTIVE-LIST-' . uniqid(),
        'nama_bidang' => 'Bidang Daftar Aktif',
        'nama_ruangan' => 'Ruang Daftar Aktif',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-ACTIVE-LIST-001',
        'nama_aset' => 'Aset Register Aktif',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Daftar Aktif',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetRegister::create([
        'kode_aset' => 'REG-DELETED-LIST-001',
        'nama_aset' => 'Aset Register Dihapus',
        'kode_barang' => 'Printer',
        'kode_urut_barang' => '002',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Daftar Aktif',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 5000000,
        'kondisi' => 'Baik',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);
    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-DELETED-LIST-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aset SMKI Dihapus',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Daftar Aktif',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Dihapus',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $registerResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index'));

    $registerResponse->assertOk();
    $registerResponse->assertSee('Aset Register Aktif');
    $registerResponse->assertDontSee('Aset Register Dihapus');

    $smkiResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-smki.index'));

    $smkiResponse->assertOk();
    $smkiResponse->assertDontSee('Aset SMKI Dihapus');
});

test('register asset stores formatted acquisition value as full rupiah amount', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-NILAI-' . uniqid(),
        'nama_bidang' => 'Bidang Register Nilai',
        'nama_ruangan' => 'Ruang Register Nilai',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.data-aset-register.store'), [
            'kode_aset' => 'REG-NILAI-001',
            'nama_aset' => 'Meja Nilai Rupiah',
            'kode_barang' => 'KB-NILAI',
            'kode_urut_barang' => '001',
            'status_barang' => 'Baik',
            'pemilik_aset' => 'Diskominfotik Riau',
            'pengguna' => 'Admin Bidang',
            'lokasi_aset' => 'Ruang Register Nilai',
            'metode_pemusnahan' => null,
            'kerahasiaan' => 'Umum',
            'kritikalitas' => 'SEDANG',
            'nilai' => 'Rp 6.500.000',
            'keterangan' => 'Nilai perolehan memakai format rupiah.',
        ]);

    $response->assertRedirect(route('admin-perbidang.data-aset-register.index'));

    $asset = AsetRegister::where('kode_aset', 'REG-NILAI-001')->firstOrFail();

    expect((float) $asset->nilai)->toBe(6500000.0);
});

test('admin perbidang cannot delete register asset from list or endpoint', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-NO-DELETE-' . uniqid(),
        'nama_bidang' => 'Bidang Register Tanpa Hapus',
        'nama_ruangan' => 'Ruang Register Tanpa Hapus',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-NO-DELETE-001',
        'nama_aset' => 'Aset Register Tidak Bisa Dihapus Admin',
        'kode_barang' => 'KB-NO-DELETE',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Register Tanpa Hapus',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $indexResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Aset Register Tidak Bisa Dihapus Admin');
    $indexResponse->assertDontSee('delete-form');
    $indexResponse->assertDontSee('Hapus Aset');

    $deleteResponse = $this->actingAs($admin)
        ->delete('/admin-perbidang/data-aset-register/' . $asset->id);

    $deleteResponse->assertMethodNotAllowed();
    expect(AsetRegister::whereKey($asset->id)->exists())->toBeTrue();
});

test('admin perbidang cannot delete smki asset from list or endpoint', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'SMKI-NO-DELETE-' . uniqid(),
        'nama_bidang' => 'Bidang SMKI Tanpa Hapus',
        'nama_ruangan' => 'Ruang SMKI Tanpa Hapus',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-NO-DELETE-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aset SMKI Tidak Bisa Dihapus Admin',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang SMKI Tanpa Hapus',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $indexResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-smki.index'));

    $indexResponse->assertOk();
    $indexResponse->assertSee('Aset SMKI Tidak Bisa Dihapus Admin');
    $indexResponse->assertDontSee('delete-form');
    $indexResponse->assertDontSee('Hapus Aset');

    $deleteResponse = $this->actingAs($admin)
        ->delete('/admin-perbidang/data-aset-smki/' . $asset->id);

    $deleteResponse->assertMethodNotAllowed();
    expect(AsetSmki::whereKey($asset->id)->exists())->toBeTrue();
});

test('admin perbidang asset lists show operational borrowed status separately from verification', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'BORROWED-STATUS-' . uniqid(),
        'nama_bidang' => 'Bidang Status Dipinjam',
        'nama_ruangan' => 'Ruang Status Dipinjam',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    AsetRegister::create([
        'kode_aset' => 'REG-BORROWED-STATUS-001',
        'nama_aset' => 'Aset Register Sedang Dipinjam',
        'kode_barang' => 'KB-BORROWED',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Status Dipinjam',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 10000000,
        'kondisi' => 'Baik',
        'status' => 'Dipinjam',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-BORROWED-STATUS-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aset SMKI Sedang Dipinjam',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Status Dipinjam',
        'penanggung_jawab' => 'Admin Bidang',
        'status' => 'Dipinjam',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $registerResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.index'));

    $registerResponse->assertOk();
    $registerResponse->assertSee('Verifikasi');
    $registerResponse->assertSee('Status Aset');
    $registerResponse->assertSee('Aset Register Sedang Dipinjam');
    $registerResponse->assertSee('Terverifikasi');
    $registerResponse->assertSee('Dipinjam');

    $smkiResponse = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-smki.index'));

    $smkiResponse->assertOk();
    $smkiResponse->assertSee('Verifikasi');
    $smkiResponse->assertSee('Status Aset');
    $smkiResponse->assertSee('Aset SMKI Sedang Dipinjam');
    $smkiResponse->assertSee('Terverifikasi');
    $smkiResponse->assertSee('Dipinjam');
});

test('admin perbidang can view read only register asset detail from own bidang', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'REG-DETAIL-' . uniqid(),
        'nama_bidang' => 'Bidang Detail Register',
        'nama_ruangan' => 'Ruang Detail Register',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-DETAIL-001',
        'nama_aset' => 'Laptop Detail Read Only',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Detail Register',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 7500000,
        'keterangan' => 'Aset untuk halaman detail read-only.',
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.show', $asset->id));

    $response->assertOk();
    $response->assertSee('Detail Aset Register');
    $response->assertSee('Laptop Detail Read Only');
    $response->assertSee('Identitas Aset');
    $response->assertSee('Riwayat Kondisi');
    $response->assertSee('Rp 7.500.000');
    $response->assertDontSee('Simpan Perubahan');
});

test('admin perbidang can view read only smki asset detail from own bidang', function () {
    $bidang = Bidang::create([
        'kode_bidang' => 'SMKI-DETAIL-' . uniqid(),
        'nama_bidang' => 'Bidang Detail SMKI',
        'nama_ruangan' => 'Ruang Detail SMKI',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);

    $asset = AsetSmki::create([
        'nomor_kode_barang' => 'SMKI-DETAIL-001',
        'jenis_barang' => 'Aplikasi',
        'merk_model' => 'Aplikasi Detail Read Only',
        'no_ser_model' => 'SN-DETAIL-001',
        'tahun_pembuatan' => 2026,
        'jumlah' => 1,
        'satuan' => 'Unit',
        'keadaan_barang' => 'Baik',
        'bidang_id' => $bidang->id,
        'ruangan' => 'Ruang Detail SMKI',
        'penanggung_jawab' => 'Admin Bidang',
        'keterangan' => 'Aset SMKI untuk detail read-only.',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $admin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-smki.show', $asset->id));

    $response->assertOk();
    $response->assertSee('Detail Aset SMKI');
    $response->assertSee('Aplikasi Detail Read Only');
    $response->assertSee('Spesifikasi dan Status');
    $response->assertSee('Riwayat Peminjaman');
    $response->assertDontSee('Simpan Perubahan');
});

test('admin perbidang cannot view asset detail from another bidang', function () {
    $ownBidang = Bidang::create([
        'kode_bidang' => 'OWN-DETAIL-' . uniqid(),
        'nama_bidang' => 'Bidang Sendiri Detail',
        'nama_ruangan' => 'Ruang Sendiri Detail',
    ]);
    $otherBidang = Bidang::create([
        'kode_bidang' => 'OTHER-DETAIL-' . uniqid(),
        'nama_bidang' => 'Bidang Lain Detail',
        'nama_ruangan' => 'Ruang Lain Detail',
    ]);
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $ownBidang->id,
    ]);
    $otherAdmin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $otherBidang->id,
    ]);

    $asset = AsetRegister::create([
        'kode_aset' => 'REG-DETAIL-FORBIDDEN',
        'nama_aset' => 'Aset Bidang Lain',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $otherBidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang Lain',
        'lokasi_aset' => 'Ruang Lain Detail',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 5000000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Terverifikasi',
        'dinput_oleh' => $otherAdmin->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin-perbidang.data-aset-register.show', $asset->id));

    $response->assertForbidden();
});
