<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JasaController;
use App\Http\Controllers\LaporanKeluarController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile dari Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // =========================
    // Barang per cabang
    // =========================
    Route::prefix('{cabang}/barang')->group(function () {
        Route::get('/', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/{id}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/{id}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

        // Report barang
        // Route::get('/report', [BarangController::class, 'report'])->name('barang.report');
        // Route::get('/report/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');
        // Export barang
        Route::get('/export/pdf', [BarangController::class, 'exportPdf'])->name('barang.export.pdf');
        Route::get('/export/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');
    });

    // Jasa
    Route::resource('jasa', JasaController::class);

    // Barang keluar (pusat)
    Route::resource('barangkeluar', BarangKeluarController::class);

    // =========================
    // L A P O R A N  (baru)
    // =========================
    Route::prefix('laporan')->name('laporan.')->group(function () {
        // ?lokasi=pusat|jt&awal=YYYY-MM-DD&akhir=YYYY-MM-DD&cari=...&mode=detail|rekap
        Route::get('/keluar', [LaporanKeluarController::class, 'index'])->name('keluar');
        Route::get('/keluar/export/pdf', [LaporanKeluarController::class, 'exportPdf'])->name('keluar.pdf');
        Route::get('/keluar/export/excel', [LaporanKeluarController::class, 'exportExcel'])->name('keluar.excel');
    });
});

// 👇 Auth route dari Breeze
require __DIR__ . '/auth.php';
