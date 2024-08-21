<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('guest')
        ->name('register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('guest')
        ->name('login');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('logout');

    Route::post('/logout-all', [AuthController::class, 'logoutAll'])
        ->middleware('auth:sanctum')
        ->name('logout');
});
