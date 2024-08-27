<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Http\Controllers\Controller;;

use App\Http\Resources\Api\V1\GameResource;
use App\Models\Game;

class GameCatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $games = Game::latest('updated_at')->paginate();
        return GameResource::collection($games);
    }

//    /**
//     * Display the specified resource.
//     */
//    public function show(Game $game)
//    {
//        //
//    }
}
