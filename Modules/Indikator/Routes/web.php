<?php

use Illuminate\Support\Facades\Route;
use Modules\Indikator\Http\Controllers\KategoriController;
use Modules\Indikator\Http\Controllers\IndikatorController;


route::middleware(['auth', 'permission'])->group(function() {

    Route::prefix('indikator')->group(function() {
        Route::get('/', [IndikatorController::class, 'index'])->name('indikator.index');
        Route::get('/tambah', [IndikatorController::class, 'create'])->name('indikator.create');
        Route::post('/tambah', [IndikatorController::class, 'store'])->name('indikator.store');
        Route::get('/{id}/edit', [IndikatorController::class, 'edit'])->name('indikator.edit');
        Route::put('/{id}', [IndikatorController::class, 'update'])->name('indikator.update');
        Route::delete('/{id}', [IndikatorController::class, 'destroy'])->name('indikator.destroy');
    });

    Route::prefix('kategori')->group(function() {
            Route::get('/', [KategoriController::class, 'index'])->name('kategori.index');
            Route::get('/tambah', [KategoriController::class, 'create'])->name('kategori.create');
            Route::post('/tambah', [KategoriController::class, 'store'])->name('kategori.store');
            Route::get('/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.edit');
            Route::put('/{id}', [KategoriController::class, 'update'])->name('kategori.update');
            Route::delete('/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
    });
    Route::prefix('unit')->group(function() {
        Route::get('/', [\Modules\Indikator\Http\Controllers\UnitController::class, 'index'])->name('unit.index');
        Route::get('/tambah', [\Modules\Indikator\Http\Controllers\UnitController::class, 'create'])->name('unit.create');
        Route::post('/tambah', [\Modules\Indikator\Http\Controllers\UnitController::class, 'store'])->name('unit.store');
        Route::get('/{id}/edit', [\Modules\Indikator\Http\Controllers\UnitController::class, 'edit'])->name('unit.edit');
        Route::put('/{id}', [\Modules\Indikator\Http\Controllers\UnitController::class, 'update'])->name('unit.update');
        Route::delete('/{id}', [\Modules\Indikator\Http\Controllers\UnitController::class, 'destroy'])->name('unit.destroy');
    });
});
