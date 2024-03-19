<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\ConfigurableProductController;

Route::prefix('product/configurable')
    ->middleware(['auth:sanctum', 'abilities.check:product-create'])
    ->group(function () {
        Route::post(
            'createOptionGroup',
            [ConfigurableProductController::class, 'createOptionGroup']
        );
        Route::post(
            'createOptionItem',
            [ConfigurableProductController::class, 'createOptionItem']
        );
        Route::post(
            'createVariantGroup',
            [ConfigurableProductController::class, 'createVariantGroup']
        );
        Route::post(
            'createVariantItem',
            [ConfigurableProductController::class, 'createVariantItem']
        );
    });

Route::prefix('product/configurable')
    ->middleware(['auth:sanctum', 'abilities.check:product-delete'])
    ->group(function () {
        Route::post(
            'deleteByType',
            [ConfigurableProductController::class, 'deleteItem']
        );
    });
