<?php

use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/exports/registros', [ExportController::class, 'registros'])->name('export.registros');
    Route::get('/admin/exports/distribuidores', [ExportController::class, 'distribuidores'])->name('export.distribuidores');
});
