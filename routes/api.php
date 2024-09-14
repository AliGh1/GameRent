<?php

use App\Http\Controllers\Api\V1\Admin\AccountController;
use App\Http\Controllers\Api\V1\Admin\GameController;
use App\Http\Controllers\Api\V1\Admin\GenreController;
use App\Http\Controllers\Api\V1\Admin\PlatformController;
use App\Http\Controllers\Api\V1\Game\GameCatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->name('api.')->group(function () {
    require __DIR__.'/auth.php';

    Route::get('/games', [GameCatalogController::class, 'index'])->name('games.index');
    Route::get('/games/{game}', [GameCatalogController::class, 'show'])->name('games.show');

    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
        Route::apiResource('/platforms', PlatformController::class)->except('show');
        Route::apiResource('/genres', GenreController::class)->except('show');

        Route::apiResource('/games', GameController::class)->except(['index', 'show']);
        Route::apiResource('/games/{game}/accounts', AccountController::class)->only('store');

    });
});
