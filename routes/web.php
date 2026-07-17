<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/qr/aset/{type}/{id}', [App\Http\Controllers\AsetQrDetailController::class, 'show'])->name('qr.asset.show');

Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    switch ($role) {
        case 'Super Admin':
            return redirect('/super-admin/dashboard');
        case 'Admin Perbidang':
            return redirect('/admin-perbidang/dashboard');
        case 'Kepala Dinas':
            return redirect('/kepala-dinas/dashboard');
        default:
            abort(403, 'Role pengguna tidak terdaftar untuk sistem ini.');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Super Admin Routes
Route::middleware(['auth', 'role:Super Admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pengguna', App\Http\Controllers\SuperAdmin\KelolaPenggunaController::class);
    Route::resource('kategori-aset', App\Http\Controllers\SuperAdmin\KategoriAsetController::class)->except(['show']);
    Route::get('/verifikasi-aset', [App\Http\Controllers\SuperAdmin\VerifikasiAsetController::class, 'index'])->name('verifikasi-aset.index');
    Route::get('/verifikasi-aset/{type}/{id}', [App\Http\Controllers\SuperAdmin\VerifikasiAsetController::class, 'show'])->name('verifikasi-aset.show');
    Route::patch('/verifikasi-aset/{type}/{id}/approve', [App\Http\Controllers\SuperAdmin\VerifikasiAsetController::class, 'approve'])->name('verifikasi-aset.approve');
    Route::patch('/verifikasi-aset/{type}/{id}/reject', [App\Http\Controllers\SuperAdmin\VerifikasiAsetController::class, 'reject'])->name('verifikasi-aset.reject');
    Route::get('/verifikasi-mutasi', [App\Http\Controllers\SuperAdmin\VerifikasiMutasiAsetController::class, 'index'])->name('verifikasi-mutasi.index');
    Route::get('/verifikasi-mutasi/{mutasi_aset}', [App\Http\Controllers\SuperAdmin\VerifikasiMutasiAsetController::class, 'show'])->name('verifikasi-mutasi.show');
    Route::patch('/verifikasi-mutasi/{mutasi_aset}/approve', [App\Http\Controllers\SuperAdmin\VerifikasiMutasiAsetController::class, 'approve'])->name('verifikasi-mutasi.approve');
    Route::patch('/verifikasi-mutasi/{mutasi_aset}/reject', [App\Http\Controllers\SuperAdmin\VerifikasiMutasiAsetController::class, 'reject'])->name('verifikasi-mutasi.reject');
    Route::get('/verifikasi-peminjaman', [App\Http\Controllers\SuperAdmin\VerifikasiPeminjamanAsetController::class, 'index'])->name('verifikasi-peminjaman.index');
    Route::get('/verifikasi-peminjaman/{peminjaman_aset}', [App\Http\Controllers\SuperAdmin\VerifikasiPeminjamanAsetController::class, 'show'])->name('verifikasi-peminjaman.show');
    Route::patch('/verifikasi-peminjaman/{peminjaman_aset}/approve', [App\Http\Controllers\SuperAdmin\VerifikasiPeminjamanAsetController::class, 'approve'])->name('verifikasi-peminjaman.approve');
    Route::patch('/verifikasi-peminjaman/{peminjaman_aset}/reject', [App\Http\Controllers\SuperAdmin\VerifikasiPeminjamanAsetController::class, 'reject'])->name('verifikasi-peminjaman.reject');
    Route::get('/penyusutan-aset', [App\Http\Controllers\SuperAdmin\PenyusutanAsetController::class, 'index'])->name('penyusutan-aset.index');
    Route::post('/penyusutan-aset/hitung', [App\Http\Controllers\SuperAdmin\PenyusutanAsetController::class, 'calculateAll'])->name('penyusutan-aset.calculate-all');
    Route::get('/penyusutan-aset/{aset_register}/jadwal', [App\Http\Controllers\SuperAdmin\PenyusutanAsetController::class, 'schedule'])->name('penyusutan-aset.schedule');
    Route::post('/penyusutan-aset/{aset_register}/hitung', [App\Http\Controllers\SuperAdmin\PenyusutanAsetController::class, 'calculate'])->name('penyusutan-aset.calculate');
    Route::get('/penghapusan-aset', [App\Http\Controllers\SuperAdmin\PenghapusanAsetController::class, 'index'])->name('penghapusan-aset.index');
    Route::post('/penghapusan-aset/{type}/{id}', [App\Http\Controllers\SuperAdmin\PenghapusanAsetController::class, 'store'])->name('penghapusan-aset.store');
    Route::get('/qr-code', [App\Http\Controllers\SuperAdmin\QrCodeController::class, 'index'])->name('qr-code.index');
    Route::post('/qr-code/{type}/{id}/generate', [App\Http\Controllers\SuperAdmin\QrCodeController::class, 'generate'])->name('qr-code.generate');
    Route::get('/qr-code/{type}/{id}/label', [App\Http\Controllers\SuperAdmin\QrCodeController::class, 'label'])->name('qr-code.label');
    Route::get('/qr-code/{type}/{id}/download', [App\Http\Controllers\SuperAdmin\QrCodeController::class, 'download'])->name('qr-code.download');
});

// Admin Perbidang Routes
Route::middleware(['auth', 'role:Admin Perbidang'])->prefix('admin-perbidang')->name('admin-perbidang.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminPerbidang\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data-aset/riwayat', [App\Http\Controllers\AdminPerbidang\RiwayatAsetController::class, 'index'])->name('data-aset.riwayat');
    Route::get('/data-aset-smki/export', [App\Http\Controllers\AdminPerbidang\DataAsetSMKIController::class, 'export'])->name('data-aset-smki.export');
    Route::get('/data-aset-register/export', [App\Http\Controllers\AdminPerbidang\DataAsetRegisterController::class, 'export'])->name('data-aset-register.export');
    Route::resource('data-aset-smki', App\Http\Controllers\AdminPerbidang\DataAsetSMKIController::class)->except(['destroy']);
    Route::resource('data-aset-register', App\Http\Controllers\AdminPerbidang\DataAsetRegisterController::class)->except(['destroy']);
    Route::resource('kondisi-aset', App\Http\Controllers\AdminPerbidang\KondisiAsetController::class);
    Route::resource('mutasi-aset', App\Http\Controllers\AdminPerbidang\MutasiAsetController::class)
        ->only(['index', 'create', 'store', 'show']);
    Route::patch('/peminjaman-aset/{peminjaman_aset}/return', [App\Http\Controllers\AdminPerbidang\PeminjamanAsetController::class, 'returnAsset'])->name('peminjaman-aset.return');
    Route::resource('peminjaman-aset', App\Http\Controllers\AdminPerbidang\PeminjamanAsetController::class)
        ->only(['index', 'create', 'store', 'show']);
});

// Kepala Dinas Routes
Route::middleware(['auth', 'role:Kepala Dinas'])->prefix('kepala-dinas')->name('kepala-dinas.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\KepalaDinas\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring-aset/data-aset', [App\Http\Controllers\KepalaDinas\MonitoringAsetController::class, 'data'])->name('monitoring-aset.data');
    Route::get('/monitoring-aset/kondisi-aset', [App\Http\Controllers\KepalaDinas\MonitoringAsetController::class, 'kondisi'])->name('monitoring-aset.kondisi');
    Route::redirect('/monitoring-aset/status-aset', '/kepala-dinas/monitoring-aset/data-aset-nonaktif');
    Route::get('/monitoring-aset/data-aset-nonaktif', [App\Http\Controllers\KepalaDinas\MonitoringAsetController::class, 'nonaktif'])->name('monitoring-aset.nonaktif');
    Route::get('/monitoring-aset/penyusutan-aset', [App\Http\Controllers\KepalaDinas\MonitoringPenyusutanController::class, 'index'])->name('monitoring-aset.penyusutan');
    Route::get('/monitoring-aset/penyusutan-aset/{asetRegister}', [App\Http\Controllers\KepalaDinas\MonitoringPenyusutanController::class, 'show'])->name('monitoring-aset.penyusutan.show');
    Route::get('/monitoring-aset/{type}/{id}', [App\Http\Controllers\KepalaDinas\MonitoringAsetController::class, 'show'])->name('monitoring-aset.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/qr/aset/{type}/{id}/label', [App\Http\Controllers\SuperAdmin\QrCodeController::class, 'label'])->name('qr.asset.label');
    Route::get('/qr/aset/{type}/{id}/download', [App\Http\Controllers\SuperAdmin\QrCodeController::class, 'download'])->name('qr.asset.download');
    Route::get('/riwayat-mutasi-aset', [App\Http\Controllers\RiwayatMutasiAsetController::class, 'index'])->name('riwayat-mutasi.index');
    Route::get('/riwayat-mutasi-aset/{mutasi_aset}', [App\Http\Controllers\RiwayatMutasiAsetController::class, 'show'])->name('riwayat-mutasi.show');
    Route::get('/laporan-aset', [App\Http\Controllers\LaporanAsetController::class, 'index'])->name('laporan-aset.index');
    Route::get('/laporan-aset/export', [App\Http\Controllers\LaporanAsetController::class, 'export'])->name('laporan-aset.export');
    Route::get('/laporan-aset/print', [App\Http\Controllers\LaporanAsetController::class, 'print'])->name('laporan-aset.print');
    Route::get('/laporan-aset/{laporan}/lihat', [App\Http\Controllers\LaporanAsetController::class, 'view'])->name('laporan-aset.view');
    Route::get('/laporan-aset/{laporan}/download', [App\Http\Controllers\LaporanAsetController::class, 'download'])->name('laporan-aset.download');
    Route::get('/upload-laporan', [App\Http\Controllers\LaporanAsetController::class, 'uploadIndex'])->name('upload-laporan.index');
    Route::post('/upload-laporan', [App\Http\Controllers\LaporanAsetController::class, 'store'])->name('upload-laporan.store');
    Route::get('/notifikasi', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifikasi/{notification}/read', [App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifikasi/read-all', [App\Http\Controllers\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
