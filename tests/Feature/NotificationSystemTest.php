<?php

use App\Models\AsetRegister;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeNotificationBidang(): Bidang
{
    return Bidang::create([
        'kode_bidang' => 'NOTIF-' . uniqid(),
        'nama_bidang' => 'Bidang Notifikasi',
        'nama_ruangan' => 'Ruang Notifikasi',
    ]);
}

function makePendingRegisterAssetForNotification(User $admin, Bidang $bidang): AsetRegister
{
    return AsetRegister::create([
        'kode_aset' => 'NOTIF-REG-' . uniqid(),
        'nama_aset' => 'Laptop Notifikasi',
        'kode_barang' => 'Laptop',
        'kode_urut_barang' => '001',
        'bidang_id' => $bidang->id,
        'status_barang' => 'Baik',
        'pemilik_aset' => 'Diskominfotik Riau',
        'pengguna' => 'Admin Bidang',
        'lokasi_aset' => 'Ruang Notifikasi',
        'kerahasiaan' => 'Umum',
        'kritikalitas' => 'SEDANG',
        'nilai' => 6500000,
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'status_verifikasi' => 'Perlu Verifikasi',
        'dinput_oleh' => $admin->id,
    ]);
}

test('admin asset submission notifies super admin', function () {
    $bidang = makeNotificationBidang();
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);

    $response = $this->actingAs($admin)
        ->post(route('admin-perbidang.data-aset-register.store'), [
            'kode_aset' => 'NOTIF-REG-001',
            'nama_aset' => 'Laptop Pengajuan Notifikasi',
            'kode_barang' => 'Laptop',
            'kode_urut_barang' => '001',
            'status_barang' => 'Baik',
            'pemilik_aset' => 'Diskominfotik Riau',
            'pengguna' => 'Admin Bidang',
            'lokasi_aset' => 'Ruang Notifikasi',
            'metode_pemusnahan' => null,
            'kerahasiaan' => 'Umum',
            'kritikalitas' => 'SEDANG',
            'nilai' => 6500000,
            'keterangan' => 'Aset untuk notifikasi.',
        ]);

    $response->assertRedirect(route('admin-perbidang.data-aset-register.index'));

    $notification = $superAdmin->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Aset Register menunggu verifikasi');
    expect($notification->data['message'])->toContain('Laptop Pengajuan Notifikasi');

    $this->actingAs($superAdmin)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Aset Register menunggu verifikasi');
});

test('asset verification decision notifies submitting admin', function () {
    $bidang = makeNotificationBidang();
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);
    $asset = makePendingRegisterAssetForNotification($admin, $bidang);

    $response = $this->actingAs($superAdmin)
        ->patch(route('super-admin.verifikasi-aset.approve', ['register', $asset->id]));

    $response->assertRedirect(route('super-admin.verifikasi-aset.index'));

    $notification = $admin->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Aset berhasil diverifikasi');
    expect($notification->data['message'])->toContain('Laptop Notifikasi');
});

test('uploaded report notifies kepala dinas', function () {
    Storage::fake('local');

    $bidang = makeNotificationBidang();
    $admin = User::factory()->create([
        'role' => 'Admin Perbidang',
        'bidang_id' => $bidang->id,
    ]);
    $kepalaDinas = User::factory()->create(['role' => 'Kepala Dinas']);

    $response = $this->actingAs($admin)
        ->post(route('upload-laporan.store'), [
            'jenis_aset' => 'Register',
            'jenis_laporan' => 'Laporan Bulanan',
            'keterangan' => 'Rekap notifikasi laporan.',
            'file' => UploadedFile::fake()->create('laporan-notifikasi.pdf', 64, 'application/pdf'),
        ]);

    $response->assertRedirect(route('upload-laporan.index'));

    $notification = $kepalaDinas->notifications()->first();

    expect($notification)->not->toBeNull();
    expect($notification->data['title'])->toBe('Laporan aset baru diupload');
    expect($notification->data['message'])->toContain('Laporan Bulanan');
});
