<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\CheckoutController;

Route::prefix('checkout')
    ->group(function () {
        Route::post('create', [CheckoutController::class, 'createOrder']);
    });
