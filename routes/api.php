<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;

Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class)
        ->only(['index']);
    Route::apiResource('orders', OrderController::class)
        ->except(['store', 'destroy', 'updateStatus']);
    Route::post('/orders', [OrderController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
});
