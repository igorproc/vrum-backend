<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\ProductController;
use \App\Http\Controllers\api\UploadController;

Route::prefix('product')->group(function () {
    Route::get(
        'list',
        [ProductController::class, 'getPage']
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
        Route::get(
            'admin/{id}',
            [ProductController::class, 'getProductById']
        );
        Route::post(
            'create',
            [ProductController::class, 'create']
        );
        Route::post(
            'uploadImage',
            [UploadController::class, 'upload']
        );
    });

Route::prefix('product')
    ->middleware([
        'auth:sanctum',
        'abilities.check:product-delete'
    ])->group(function () {
        Route::post(
            'deleteImage',
            [UploadController::class, 'delete']
        );
        Route::post(
            'delete/{id}',
            [ProductController::class, 'delete']
        );
    });

Route::prefix('product')
    ->middleware([
        'auth:sanctum',
        'abilities.check:product-update'
    ])->group(function () {
        Route::post('update', [ProductController::class, 'update']);
    });
