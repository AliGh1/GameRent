<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\NewPasswordController;
use App\Http\Controllers\Api\V1\PasswordResetLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('guest.api')
        ->name('register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('guest.api')
        ->name('login');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('guest.api')
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('guest.api')
        ->name('password.store');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('logout');

    Route::post('/logout-all', [AuthController::class, 'logoutAll'])
        ->middleware('auth:sanctum')
        ->name('logout');
});
