<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\P2pOrderController;
use App\Http\Controllers\P2pTradeController;
use App\Http\Controllers\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::get('/{user}', [UserController::class, 'show']);
    Route::get('/{user}/wallets', [WalletController::class, 'index']);
    Route::get('/{user}/transactions', [TransactionController::class, 'index']);
});

Route::prefix('wallets')->group(function () {
    Route::get('/{wallet}', [WalletController::class, 'show']);
});

Route::prefix('p2p-orders')->group(function () {
    Route::get('/', [P2pOrderController::class, 'index']);
    Route::post('/', [P2pOrderController::class, 'store']);
    Route::get('/{order}', [P2pOrderController::class, 'show']);
});

Route::prefix('p2p-trades')->group(function () {
    Route::post('/', [P2pTradeController::class, 'store']);
    Route::get('/{trade}', [P2pTradeController::class, 'show']);
    Route::put('/{trade}/status', [P2pTradeController::class, 'updateStatus']);
});

Route::prefix('transactions')->group(function () {
    Route::post('/', [TransactionController::class, 'store']);
});
