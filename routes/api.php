<?php

use App\Http\Controllers\Api\DistribuidorController;
use App\Http\Controllers\Api\SorteoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:100,1')->group(function () {

    Route::prefix('sorteos/{sorteo}')->group(function () {
        Route::get('info', [SorteoController::class, 'info']);
        Route::get('participante/{cedula}', [SorteoController::class, 'consultarParticipante']);

        Route::middleware('throttle:10,1')->group(function () {
            Route::post('participar', [SorteoController::class, 'participar']);
        });
    });

    Route::post('distribuidores', [DistribuidorController::class, 'store']);
});
