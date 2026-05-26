<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
});

// Admin Perbidang Routes
Route::middleware(['auth', 'role:Admin Perbidang'])->prefix('admin-perbidang')->name('admin-perbidang.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminPerbidang\DashboardController::class, 'index'])->name('dashboard');
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
