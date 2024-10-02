<?php

use App\Http\Controllers\StaticAssetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/static-asset/{path}', [StaticAssetController::class, 'index'])
        ->where('path', '.*')
        ->name('static-asset')
        ->middleware('cache.headers:public;max_age=2628000;etag');
});
