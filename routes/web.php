<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\LaporanBarangKeluarMobilController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JasaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanKeluarController;
use App\Http\Controllers\PendapatanMobilController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manage Users
    Route::resource('users', UserController::class);

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

        Route::get('/bulk', [BarangController::class, 'bulkForm'])->name('barang.bulk.form');
        Route::post('/bulk', [BarangController::class, 'bulkUpdate'])->name('barang.bulk.update');
        Route::get('/merk-list', [BarangController::class, 'merkList'])->name('barang.merk.list');

        // Export barang
        Route::get('/export/pdf', [BarangController::class, 'exportPdf'])->name('barang.export.pdf');
        Route::get('/export/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');
    });

    // Jasa
    Route::resource('jasa', JasaController::class);

    // Barang keluar (pusat)
    Route::prefix('{cabang}')->group(function () {
        Route::resource('barangkeluar', BarangKeluarController::class);
    });

    // =========================
    // L A P O R A N  (puasat, jayanti timur, ruko)
    // =========================
    Route::prefix('laporan')->name('laporan.')->group(function () {
        // ?lokasi=pusat|jt|ruko&awal=YYYY-MM-DD&akhir=YYYY-MM-DD&cari=...&mode=detail|rekap
        Route::get('/keluar', [LaporanKeluarController::class, 'index'])->name('keluar');
        Route::get('/keluar/export/pdf', [LaporanKeluarController::class, 'exportPdf'])->name('keluar.pdf');
        Route::get('/keluar/export/excel', [LaporanKeluarController::class, 'exportExcel'])->name('keluar.excel');
    });

    // =========================
    // L A P O R A N  (mobil)
    // =========================
    Route::prefix('laporan/mobil')->name('laporan.mobil.')->group(function () {
        // Rekap Transaksi (harian)
        Route::get('/rekap-transaksi', [LaporanBarangKeluarMobilController::class, 'index'])->name('rekap');
        Route::get('/rekap-transaksi/export/pdf', [LaporanBarangKeluarMobilController::class, 'exportPdf'])->name('rekap.pdf');
        Route::get('/rekap-transaksi/export/excel', [LaporanBarangKeluarMobilController::class, 'exportExcel'])->name('rekap.excel');

        // Barang Keluar (dari penjualan transbrg)
        Route::get('/barang-keluar', [LaporanBarangKeluarMobilController::class, 'index'])->name('keluar');
        Route::get('/barang-keluar/export/pdf', [LaporanBarangKeluarMobilController::class, 'exportPdf'])->name('keluar.pdf');
        Route::get('/barang-keluar/export/excel', [LaporanBarangKeluarMobilController::class, 'exportExcel'])->name('keluar.excel');

        // Pendapatan Mobil
        Route::get('/pendapatan', [PendapatanMobilController::class, 'index'])->name('pendapatan');
        Route::get('/pendapatan/export/pdf', [PendapatanMobilController::class, 'exportPdf'])->name('pendapatan.pdf');
        Route::get('/pendapatan/export/excel', [PendapatanMobilController::class, 'exportExcel'])->name('pendapatan.excel');
    });
});

// 👇 Auth route dari Breeze
require __DIR__ . '/auth.php';
