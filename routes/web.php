<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JasaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// 👇 Welcome page (optional, bisa kamu ganti nanti)
Route::get('/', function () {
    return view('welcome');
});

// 👇 Semua route yang butuh login
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile dari Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Barang per cabang
    Route::prefix('{cabang}/barang')->group(function () {
        Route::get('/', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/{id}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/{id}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

        // Report barang
        Route::get('/report', [BarangController::class, 'report'])->name('barang.report');
        Route::get('/report/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');
    });

    // Jasa
    Route::resource('jasa', JasaController::class);

    // Barang keluar
    Route::resource('barangkeluar', BarangKeluarController::class);
});

// 👇 Auth route dari Breeze
require __DIR__ . '/auth.php';
