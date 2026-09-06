<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\Assessment\Http\Controllers\AssessmentController;
Use Modules\Assessment\Http\Controllers\PeriodeController;
use Modules\Assessment\Http\Controllers\LaporanController;

Route::middleware(['auth'])->group(function() {
    Route::prefix('Assessment')->group(function() {
        Route::get('/', [AssessmentController::class, 'index'])->name('assessment.index');
        Route::get('/form', [AssessmentController::class, 'create'])->name('assessment.create');
        Route::post('/store', [AssessmentController::class, 'store'])->name('assessment.store');
        Route::get('/reload-previous', [AssessmentController::class, 'reloadPrevious'])->name('assessment.reload-previous');
        Route::get('/export', [AssessmentController::class, 'exportExcel'])->name('assessment.export');
        Route::get('/riwayat', [AssessmentController::class, 'riwayat'])->name('assessment.riwayat');

    });

        Route::prefix('periode')->group(function() {
            Route::get('/', [PeriodeController::class, 'index'])->name('periode.index')->middleware('permission:periode.index');
            Route::get('/create', [PeriodeController::class, 'create'])->name('periode.create')->middleware('permission:periode.create');
            Route::post('/store', [PeriodeController::class, 'store'])->name('periode.store')->middleware('permission:periode.store');
            Route::patch('/{id}/end', [PeriodeController::class, 'end'])->name('periode.end')->middleware('permission:periode.end');
        });

        Route::prefix('Laporan')->group(function(){
            Route::get('/', [LaporanController::class, 'index'])->name('laporan.index');
            Route::get('/preview', [LaporanController::class, 'preview'])->name('laporan.preview');
            Route::get('/download', [LaporanController::class, 'download'])->name('laporan.download');
        });


        
});



