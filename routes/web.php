<?php

use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\StaticAssetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/static-asset/{path}', [StaticAssetController::class, 'index'])
        ->where('path', '.*')
        ->name('static-asset')
        ->middleware('cache.headers:public;max_age=2628000;etag');
});

Route::post('/account/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware('throttle:6,1')
    ->name('verification.send');
Route::get('/account/verify', [EmailVerificationController::class, 'unverifiedAccount'])
    ->name('verification.notice');
Route::get('/account/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');
