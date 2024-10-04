<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AccountController::class, 'index'])->name('account');
    Route::put('account', [AccountController::class, 'update'])->name('account.update');

    Route::get('chats', [ChatController::class, 'index'])->name('chats.index');
    Route::post('chats', [ChatController::class, 'store'])->name('chats.store');
    Route::put('chats/{chat}', [ChatController::class, 'update'])->name('chats.update');
    Route::delete('chats/{chat}', [ChatController::class, 'destroy'])->name('chats.destroy');
    Route::patch('chats/{chat}/add-participant/{user}', [ChatController::class, 'addParticipant'])->name('chats.add-participant');
    Route::delete('chats/{chat}/remove-participant/{user}', [ChatController::class, 'removeParticipant'])->name('chats.remove-participant');
    Route::patch('chats/{chat}/make-admin/{user}', [ChatController::class, 'makeAsAdmin'])->name('chats.make-admin');
    Route::patch('chats/{chat}/dismiss-admin/{user}', [ChatController::class, 'dismissAsAdmin'])->name('chats.dismiss-admin');
    Route::put('chats/{chat}/update-avatar', [ChatController::class, 'updateAvatar'])->name('chats.update-avatar');

    Route::get('chats/{chat}/messages', [MessageController::class, 'index'])->name('chats.messages.index');
    Route::post('chats/{chat}/messages', [MessageController::class, 'store'])->name('chats.messages.store');
    Route::put('messages/{message}', [MessageController::class, 'update'])->name('chats.messages.update');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('chats.messages.destroy');
});
