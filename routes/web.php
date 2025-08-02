<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JasaController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('report/barang', [BarangController::class, 'report'])->name('barang.report');
// Route::get('report/barang/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');
// Route::resource('barang', BarangController::class);

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('{cabang}/barang')->group(function () {
    Route::get('/', [BarangController::class, 'index'])->name('barang.index');
    Route::get('/create', [BarangController::class, 'create'])->name('barang.create');
    Route::post('/', [BarangController::class, 'store'])->name('barang.store');
    Route::get('/{id}/edit', [BarangController::class, 'edit'])->name('barang.edit');
    Route::put('/{id}', [BarangController::class, 'update'])->name('barang.update');
    Route::delete('/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

    Route::get('/report', [BarangController::class, 'report'])->name('barang.report');
    Route::get('/report/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');
});

Route::resource('jasa', JasaController::class);
Route::resource('barangkeluar', BarangKeluarController::class);
