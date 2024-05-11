<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\CheckoutController;

Route::prefix('checkout')
    ->group(function () {
        Route::post('create', [CheckoutController::class, 'createOrder']);
    });

Route::prefix('checkout')
    ->middleware([
        'auth:sanctum',
        'abilities.check:product-create,product-update,product-update'
    ])->group(function () {
        Route::get('list', [CheckoutController::class, 'getPage']);

        Route::post('update/status', [CheckoutController::class, 'updateOrderStatus']);
    });
