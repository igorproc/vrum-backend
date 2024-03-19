<?php

use Illuminate\Support\Facades\Route;

use \App\Http\Controllers\api\WishlistController;

Route::prefix('user/wishlist')
    ->group(function () {
        Route::get(
            'shortData',
            [WishlistController::class, 'getShortData']
        );

        Route::get(
            'list',
            [WishlistController::class, 'getProducts']
        );

        Route::post(
            'createCart',
            [WishlistController::class, 'createCart']
        );

        Route::post(
            'addItem',
            [WishlistController::class, 'addItemToCart']
        );

        Route::post(
            'removeItem',
            [WishlistController::class, 'removeItemFromCart']
        );
    });
