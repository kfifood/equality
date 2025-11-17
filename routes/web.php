<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\PenggunaanController;
use App\Http\Controllers\PerbaikanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\TimbanganController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/login');

// Authentication Routes
Auth::routes(['register' => false]);

// RFID Login Route
Route::post('/login/rfid', [App\Http\Controllers\Auth\LoginController::class, 'loginWithRfid'])->name('login.rfid');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    });

    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('logout');

    // ==================== USER MANAGEMENT ====================
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::put('/{id}/status', [UserController::class, 'updateStatus'])->name('users.update-status');
        Route::put('/{id}/password', [UserController::class, 'changePassword'])->name('users.change-password');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::resource('timbangan', TimbanganController::class);
    // ==================== MASTER DATA ====================
    // Timbangan Routes - SEMUA ROUTE TIMBANGAN DI SINI
    Route::prefix('timbangan')->group(function () {
        
        Route::get('/', [TimbanganController::class, 'index'])->name('timbangan.index');
        Route::get('/create', [TimbanganController::class, 'create'])->name('timbangan.create');
        Route::post('/', [TimbanganController::class, 'store'])->name('timbangan.store');
        Route::get('/{id}/edit', [TimbanganController::class, 'edit'])->name('timbangan.edit');
        Route::put('/{id}', [TimbanganController::class, 'update'])->name('timbangan.update');
        Route::delete('/{id}', [TimbanganController::class, 'destroy'])->name('timbangan.destroy');
        Route::get('/{id}/riwayat', [TimbanganController::class, 'riwayat'])->name('timbangan.riwayat');
        Route::post('/import', [TimbanganController::class, 'import'])->name('timbangan.import');
        Route::get('/export', [TimbanganController::class, 'export'])->name('timbangan.export');
        Route::get('/template', [TimbanganController::class, 'downloadTemplate'])->name('timbangan.download-template');
        Route::post('/{id}/tandai-rusak', [TimbanganController::class, 'tandaiRusak'])->name('timbangan.tandai-rusak');


    });

    // Line Routes
Route::prefix('line')->group(function () {
    Route::get('/', [LineController::class, 'index'])->name('line.index');
    Route::post('/', [LineController::class, 'store'])->name('line.store');
    Route::put('/{id}', [LineController::class, 'update'])->name('line.update');
    Route::delete('/{id}', [LineController::class, 'destroy'])->name('line.destroy');
    
    // KOREKSI: Route untuk melihat timbangan di line
    Route::get('/{id}/timbangan', [LineController::class, 'timbangan'])->name('line.timbangan');
});

    // ==================== OPERATIONS ====================
    // Penggunaan Timbangan - SEMUA ROUTE PENGGUNAAN DI SINI
    Route::prefix('penggunaan')->group(function () {
        Route::get('/', [PenggunaanController::class, 'index'])->name('penggunaan.index');
        Route::get('/create', [PenggunaanController::class, 'create'])->name('penggunaan.create');
        Route::get('/create/{timbangan_id}', [PenggunaanController::class, 'create'])->name('penggunaan.create.withId');
        Route::post('/', [PenggunaanController::class, 'store'])->name('penggunaan.store');
    });

    // Perbaikan Timbangan - SEMUA ROUTE PERBAIKAN DI SINI
    Route::prefix('perbaikan')->group(function () {
        Route::get('/', [PerbaikanController::class, 'index'])->name('perbaikan.index');
        Route::get('/create', [PerbaikanController::class, 'create'])->name('perbaikan.create');
        Route::get('/create/{timbangan_id}', [PerbaikanController::class, 'create'])->name('perbaikan.create.withId');
        Route::post('/', [PerbaikanController::class, 'store'])->name('perbaikan.store');
        Route::put('/{id}/status', [PerbaikanController::class, 'updateStatus'])->name('perbaikan.updateStatus');
        // routes/web.php
Route::get('/perbaikan/timbangan/{id}', [PerbaikanController::class, 'getTimbanganData'])->name('perbaikan.timbangan.data');
    });

    // ==================== MONITORING ====================
    // Riwayat Routes
    Route::get('/monitoring/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/monitoring/riwayat/timeline', [RiwayatController::class, 'timeline'])->name('riwayat.timeline');
    Route::get('/monitoring/riwayat/timbangan/{id}', [RiwayatController::class, 'timbangan'])->name('riwayat.timbangan');

    // ==================== REPORTS ===================

    // Reports Routes
Route::prefix('reports')->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/statistik', [LaporanController::class, 'statistik'])->name('laporan.statistik');
    Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
    Route::get('/laporan/template', [LaporanController::class, 'downloadTemplate'])->name('laporan.download-template');
});
});

// Fallback route
Route::fallback(function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect('/login');
});
