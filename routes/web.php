<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\PenggunaanController;
use App\Http\Controllers\PerbaikanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\PeralatanController;
use App\Http\Controllers\MasterKategoriAlatController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PicController;
use App\Http\Controllers\KalibrasiController;

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
Route::middleware(['auth', \App\Http\Middleware\RestrictGuestAccess::class])->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', fn() => redirect()->route('dashboard'));

    // ── Logout ─────────────────────────────────────────────────────────────
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('logout');

    // ==================== USER MANAGEMENT ====================
    Route::prefix('users')->group(function () {
        Route::get('/',              [UserController::class, 'index'])->name('users.index');
        Route::get('/create',        [UserController::class, 'create'])->name('users.create');
        Route::post('/',             [UserController::class, 'store'])->name('users.store');
        Route::get('/{id}/edit',     [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{id}',          [UserController::class, 'update'])->name('users.update');
        Route::put('/{id}/status',   [UserController::class, 'updateStatus'])->name('users.update-status');
        Route::put('/{id}/password', [UserController::class, 'changePassword'])->name('users.change-password');
        Route::delete('/{id}',       [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ==================== MASTER DATA: KATEGORI ALAT ====================
    Route::prefix('master/kategori-alat')->group(function () {
        Route::get('/',            [MasterKategoriAlatController::class, 'index'])->name('master-kategori-alat.index');
        Route::post('/',           [MasterKategoriAlatController::class, 'store'])->name('master-kategori-alat.store');
        Route::get('/list-aktif',  [MasterKategoriAlatController::class, 'listAktif'])->name('master-kategori-alat.list-aktif');
        Route::get('/{id}/edit',   [MasterKategoriAlatController::class, 'edit'])->name('master-kategori-alat.edit');
        Route::put('/{id}',        [MasterKategoriAlatController::class, 'update'])->name('master-kategori-alat.update');
        Route::delete('/{id}',     [MasterKategoriAlatController::class, 'destroy'])->name('master-kategori-alat.destroy');
    });

    // ==================== MASTER DATA: PERALATAN ====================
    // (rename dari TIMBANGAN — route Route::resource lama dibuang karena
    // create()/edit() controller ini return JSON untuk modal, bukan full page)
    Route::prefix('peralatan')->group(function () {
        Route::get('/',                    [PeralatanController::class, 'index'])->name('peralatan.index');
        Route::get('/create',              [PeralatanController::class, 'create'])->name('peralatan.create');
        Route::post('/',                   [PeralatanController::class, 'store'])->name('peralatan.store');
        Route::get('/{id}/edit',           [PeralatanController::class, 'edit'])->name('peralatan.edit');
        Route::put('/{id}',                [PeralatanController::class, 'update'])->name('peralatan.update');
        Route::delete('/{id}',             [PeralatanController::class, 'destroy'])->name('peralatan.destroy');
        Route::get('/{id}/riwayat',        [PeralatanController::class, 'riwayat'])->name('peralatan.riwayat');
        Route::get('/{id}/detail',         [PeralatanController::class, 'detail'])->name('peralatan.detail');
        Route::post('/import',             [PeralatanController::class, 'import'])->name('peralatan.import');
        Route::get('/export',              [PeralatanController::class, 'export'])->name('peralatan.export');
        Route::get('/template',            [PeralatanController::class, 'downloadTemplate'])->name('peralatan.download-template');
        Route::post('/{id}/tandai-rusak',  [PeralatanController::class, 'tandaiRusak'])->name('peralatan.tandai-rusak');
    });

    // ==================== MASTER DATA: LINE ====================
    Route::prefix('line')->group(function () {
        Route::get('/',               [LineController::class, 'index'])->name('line.index');
        Route::post('/',              [LineController::class, 'store'])->name('line.store');
        Route::put('/{id}',           [LineController::class, 'update'])->name('line.update');
        Route::delete('/{id}',        [LineController::class, 'destroy'])->name('line.destroy');
        // NOTE: method 'timbangan' di LineController belum aku lihat filenya —
        // nama method dibiarkan seperti semula, cuma path URL & nama route yang dirapikan.
        Route::get('/{id}/peralatan', [LineController::class, 'timbangan'])->name('line.peralatan');
    });

    // ==================== MASTER: PIC ====================
    Route::prefix('pic')->group(function () {
        Route::get('/',           [PicController::class, 'index'])->name('pic.index');
        Route::post('/',          [PicController::class, 'store'])->name('pic.store');
        Route::put('/{id}',       [PicController::class, 'update'])->name('pic.update');
        Route::delete('/{id}',    [PicController::class, 'destroy'])->name('pic.destroy');
        Route::get('/list-aktif', [PicController::class, 'listAktif'])->name('pic.list-aktif');
    });

    // ==================== MASTER: KELUHAN ====================
    Route::prefix('master/keluhan')->group(function () {
        Route::get('/',        [App\Http\Controllers\MasterKeluhanController::class, 'index'])->name('master-keluhan.index');
        Route::post('/',       [App\Http\Controllers\MasterKeluhanController::class, 'store'])->name('master-keluhan.store');
        Route::get('/{id}',    [App\Http\Controllers\MasterKeluhanController::class, 'edit'])->name('master-keluhan.edit');
        Route::put('/{id}',    [App\Http\Controllers\MasterKeluhanController::class, 'update'])->name('master-keluhan.update');
        Route::delete('/{id}', [App\Http\Controllers\MasterKeluhanController::class, 'destroy'])->name('master-keluhan.destroy');
    });

    // ==================== MASTER: TINDAKAN ====================
    Route::prefix('master/tindakan')->group(function () {
        Route::get('/',        [App\Http\Controllers\MasterTindakanController::class, 'index'])->name('master-tindakan.index');
        Route::post('/',       [App\Http\Controllers\MasterTindakanController::class, 'store'])->name('master-tindakan.store');
        Route::get('/{id}',    [App\Http\Controllers\MasterTindakanController::class, 'edit'])->name('master-tindakan.edit');
        Route::put('/{id}',    [App\Http\Controllers\MasterTindakanController::class, 'update'])->name('master-tindakan.update');
        Route::delete('/{id}', [App\Http\Controllers\MasterTindakanController::class, 'destroy'])->name('master-tindakan.destroy');
    });

    // ==================== OPERATIONS: PENGGUNAAN ====================
    Route::prefix('penggunaan')->group(function () {
        Route::get('/',                      [PenggunaanController::class, 'index'])->name('penggunaan.index');
        Route::get('/create',                [PenggunaanController::class, 'create'])->name('penggunaan.create');
        Route::get('/create/{peralatan_id}', [PenggunaanController::class, 'create'])->name('penggunaan.create.withId');
        Route::post('/',                     [PenggunaanController::class, 'store'])->name('penggunaan.store');
    });
    Route::get('/penggunaan/{id}/laporan-data',
        [PenggunaanController::class, 'getPenggunaanUntukLaporan']
    )->name('penggunaan.laporan-data');

    // ==================== OPERATIONS: PERBAIKAN ====================
    Route::prefix('perbaikan')->group(function () {
        Route::get('/',             [PerbaikanController::class, 'index'])->name('perbaikan.index');
        // NOTE: create(), create.withId, dan getTimbanganData() sudah dihapus dari
        // PerbaikanController — dead code peninggalan alur lama (pakai model Timbangan
        // yang sudah tidak ada) dan tidak dipanggil dari view manapun di alur baru.
        Route::post('/',            [PerbaikanController::class, 'store'])->name('perbaikan.store');
        Route::put('/{id}/status',  [PerbaikanController::class, 'updateStatus'])->name('perbaikan.updateStatus');
    });
    Route::get('/perbaikan/{laporan_id}/proses',   [App\Http\Controllers\PerbaikanController::class, 'prosesModal'])->name('perbaikan.proses-modal');
    Route::post('/perbaikan/{laporan_id}/proses',  [App\Http\Controllers\PerbaikanController::class, 'prosesStore'])->name('perbaikan.proses-store');
    Route::get('/perbaikan/{laporan_id}/detail',   [App\Http\Controllers\PerbaikanController::class, 'detail'])->name('perbaikan.detail');

    // ==================== OPERATIONS: LAPORAN KERUSAKAN ====================
    Route::prefix('laporan-kerusakan')->group(function () {
        Route::get('/{penggunaan_id}/create', [App\Http\Controllers\LaporanKerusakanController::class, 'create'])->name('laporan-kerusakan.create');
        Route::post('/',                      [App\Http\Controllers\LaporanKerusakanController::class, 'store'])->name('laporan-kerusakan.store');
    });

    // ==================== MONITORING ====================
    Route::get('/monitoring/riwayat',               [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/monitoring/riwayat/timeline',      [RiwayatController::class, 'timeline'])->name('riwayat.timeline');
    // NOTE: method di RiwayatController belum aku lihat filenya — nama method
    // dibiarkan seperti semula (timbangan), cuma path URL & nama route dirapikan.
    Route::get('/monitoring/riwayat/peralatan/{id}',[RiwayatController::class, 'timbangan'])->name('riwayat.peralatan');

    // ==================== REPORTS ====================
    Route::prefix('reports')->group(function () {
        Route::get('/laporan',           [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/statistik', [LaporanController::class, 'statistik'])->name('laporan.statistik');
        Route::get('/laporan/export',    [LaporanController::class, 'export'])->name('laporan.export');
        Route::get('/laporan/template',  [LaporanController::class, 'downloadTemplate'])->name('laporan.download-template');
    });

    // ==================== KALIBRASI ====================
    // PENTING: route eksplisit harus SEBELUM Route::resource agar tidak konflik dengan /{id}

    // Sticker (GET dulu sebelum resource menangkap /{id})
    Route::get('/kalibrasi/import-template', [KalibrasiController::class, 'importTemplate'])->name('kalibrasi.importTemplate');
    Route::get('/kalibrasi/import-modal',    [KalibrasiController::class, 'importModal'])->name('kalibrasi.importModal');
    Route::get('/kalibrasi/bulk',            [KalibrasiController::class, 'bulk'])->name('kalibrasi.bulk');
    Route::get('/kalibrasi/{id}/sticker',    [KalibrasiController::class, 'sticker'])->name('kalibrasi.sticker');

    // POST eksplisit
    Route::post('/kalibrasi/sticker-batch', [KalibrasiController::class, 'stickerBatch'])->name('kalibrasi.sticker.batch');
    Route::post('/kalibrasi/bulk',          [KalibrasiController::class, 'storeBulk'])->name('kalibrasi.storeBulk');
    Route::post('/kalibrasi/import',        [KalibrasiController::class, 'import'])->name('kalibrasi.import');

    // Resource (menangkap index, create, store, edit, update, destroy)
    Route::resource('kalibrasi', KalibrasiController::class)->except(['show']);

    // ==================== FALLBACK ====================
    // HARUS paling bawah, setelah semua route didefinisikan
    Route::fallback(function () {
        if (Auth::check()) {
            return redirect()->route(
                Auth::user()->role === 'guest' ? 'kalibrasi.index' : 'dashboard'
            );
        }
        return redirect('/login');
    });
});