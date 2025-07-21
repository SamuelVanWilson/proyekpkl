<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;

Route::get('/', [BarangController::class, 'index'])->name('barang.index');
// Route::get('/', [BarangController::class, 'index'])->name('barang.index');
// Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
// Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');
// Route::get('/barang/export/pdf', [BarangController::class, 'exportPdf'])->name('barang.export.pdf');
