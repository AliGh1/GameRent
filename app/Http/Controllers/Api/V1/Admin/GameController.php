<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Game\StoreGameRequest;
use App\Http\Requests\Api\V1\Game\UpdateGameRequest;
use App\Http\Resources\Api\V1\GameDetailResource;
use App\Models\Game;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGameRequest $request)
    {
        $gameSlug = Str::slug($request->title);

        $imagePath = $request->file('image')->store("images/games/{$gameSlug}", 'public');

        DB::beginTransaction();

        try {
            $game = Game::create([
                'title' => $request->title,
                'description' => $request->description,
                'slug' => $gameSlug,
                'release_date' => $request->release_date,
                'age_rating' => $request->age_rating,
                'image_url' => $imagePath,
                'weekly_online_price' => $request->weekly_online_price,
                'weekly_online_offline_price' => $request->weekly_online_offline_price,
            ]);

            $game->genres()->attach($request->genres);
            $game->platforms()->attach($request->platforms);

            DB::commit();

            return $this->success('Game created successfully', new GameDetailResource($game), 201);

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return $this->error('Something went wrong, please try again later', 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGameRequest $request, Game $game)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        //
    }
}
