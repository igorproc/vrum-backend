<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\CartController;

Route::prefix('cart')
    ->group(function () {
        Route::get('shortData', [CartController::class, 'getShortData']);
        Route::get('list', [CartController::class, 'getProducts']);

        Route::post('createCart', [CartController::class, 'createCart']);
        Route::post('addItem', [CartController::class, 'addItemToCart']);
        Route::post('removeItem', [CartController::class, 'removeItemFromCart']);
        Route::post('changeQuantity', [CartController::class, 'changeItemQty']);
    });
