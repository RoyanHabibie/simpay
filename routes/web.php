<?php

use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('report/barang', [BarangController::class, 'report'])->name('barang.report');
Route::get('report/barang/pdf', [BarangController::class, 'exportPdf'])->name('barang.export.pdf');
Route::get('report/barang/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');

Route::resource('barang', BarangController::class);
