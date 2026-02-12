<?php

use App\Http\Controllers\PackageApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('packages')->group(function () {
    Route::post('/', [PackageApiController::class, 'create']);
    Route::get('/', [PackageApiController::class, 'index']);
    Route::get('/{trackingNumber}/track', [PackageApiController::class, 'track']);
    Route::get('/{trackingNumber}', [PackageApiController::class, 'show']);
    Route::patch('/location', [PackageApiController::class, 'updateLocation']);
    Route::post('/cancel', [PackageApiController::class, 'cancel']);
});
