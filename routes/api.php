<?php

use App\Http\Controllers\Api\V1\Game\GameCatalogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    require __DIR__.'/auth.php';

    Route::get('/games', [GameCatalogController::class, 'index'])->name('games.index');
//    Route::get('/games/{game}', [GameCatalogController::class, 'show'])->name('games.show');
});
