<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\BrandController;
use \App\Http\Controllers\api\UploadController;

Route::prefix('brand')->
    middleware([
        'auth:sanctum',
        'abilities.check:brand-create,brand-update,brand-update',
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

    Route::post(
        'delete',
        [BrandController::class, 'delete']
    );

    Route::post(
        'update',
        [BrandController::class, 'update']
    );
});
