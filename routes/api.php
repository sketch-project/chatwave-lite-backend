<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
    Route::post('chats', [ChatController::class, 'store'])->name('chats.store');
    Route::put('chats/{chat}', [ChatController::class, 'update'])->name('chats.update');
    Route::delete('chats/{chat}', [ChatController::class, 'destroy'])->name('chats.destroy');
    Route::patch('chats/{chat}/add-participant/{user}', [ChatController::class, 'addParticipant'])->name('chats.add-participant');
    Route::patch('chats/{chat}/remove-participant/{user}', [ChatController::class, 'removeParticipant'])->name('chats.remove-participant');

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
