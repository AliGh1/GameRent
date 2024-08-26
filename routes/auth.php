<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EmailVerificationNotificationController;
use App\Http\Controllers\Api\V1\NewPasswordController;
use App\Http\Controllers\Api\V1\PasswordResetLinkController;
use App\Http\Controllers\Api\V1\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.api')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->name('register');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::post('/logout-all', [AuthController::class, 'logoutAll'])
        ->name('logout');
});
