<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
            return redirect('/user/dashboard');
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
});

// User Routes
Route::middleware(['auth', 'role:User'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/riwayat-mutasi-aset', [App\Http\Controllers\RiwayatMutasiAsetController::class, 'index'])->name('riwayat-mutasi.index');
    Route::get('/riwayat-mutasi-aset/{mutasi_aset}', [App\Http\Controllers\RiwayatMutasiAsetController::class, 'show'])->name('riwayat-mutasi.show');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
