<?php

use Illuminate\Support\Facades\Route;

use \App\Http\Controllers\api\UserController;

Route::prefix('user')->group(function () {
    Route::get(
        'data',
        [UserController::class, 'get']
    );

    Route::post(
        'create',
        [UserController::class, 'create']
    );

    Route::post(
        'login',
        [UserController::class, 'login']
    );
});
