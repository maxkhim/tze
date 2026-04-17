<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;

Route::prefix('v1')->group(function () {
    //Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    //

    /*Route::post('orders', [OrderController::class, 'store'])
        ->middleware('throttle:10,1');

    Route::apiResource('orders', OrderController::class)
        ->except(['store', 'destroy']);
*/
    Route::apiResource('products', ProductController::class)->only(['index']);
    Route::post('/orders', [OrderController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
});
