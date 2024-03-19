<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\BrandController;
use \App\Http\Controllers\api\UploadController;

Route::prefix('brand')->
    middleware([
        'auth:sanctum',
        'abilities.check:brand-create',
    ])
    ->group(function () {
    Route::post(
        'uploadImage',
        [UploadController::class, 'brand']
    );

    Route::post(
        'create',
        [BrandController::class, 'create']
    );
});

Route::prefix('brand')->
    middleware([
        'auth:sanctum',
        'abilities.check:brand-update',
    ])->group(function () {
    Route::post(
        'update',
        [BrandController::class, 'update']
    );
});

Route::prefix('brand')->
    middleware([
        'auth:sanctum',
        'abilities.check:brand-delete',
    ])->group(function () {
    Route::post(
        'delete',
        [BrandController::class, 'delete']
    );
});
