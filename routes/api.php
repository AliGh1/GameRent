<?php

use App\Http\Controllers\Api\V1\Admin\GenreController;
use App\Http\Controllers\Api\V1\Admin\PlatformController;
use App\Http\Controllers\Api\V1\Game\GameCatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__.'/auth.php';

    Route::get('/games', [GameCatalogController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [GameCatalogController::class, 'show'])->name('games.show');

    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::apiResource('/platforms', PlatformController::class)->except('show');
        Route::apiResource('/genres', GenreController::class)->except('show');
    });
});
