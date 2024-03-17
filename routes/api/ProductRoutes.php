<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\ProductController;

Route::prefix('product')->group(function () {
    Route::get(
        'all',
        [ProductController::class, 'getAll']
    );
    Route::get(
        '{name}',
        [ProductController::class, 'getByName']
    );
});

Route::prefix('product')
    ->middleware([
        'auth:sanctum',
        'abilities.check:product-create,product-update,product-update'
    ])->group(function () {
        Route::post(
            'create',
            [ProductController::class, 'create']
        );
        Route::post(
            'delete/{id}',
            [ProductController::class, 'delete']
        );
    });
