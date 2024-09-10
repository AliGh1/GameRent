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
        $gameSlug = Str::slug($request->input('title'));

        $imagePath = $request->file('image')->store("images/games/{$gameSlug}", 'public');

        DB::beginTransaction();

        try {
            $game = Game::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'slug' => $gameSlug,
                'release_date' => $request->input('release_date'),
                'age_rating' => $request->input('age_rating'),
                'image_url' => $imagePath,
                'weekly_online_price' => $request->input('weekly_online_price'),
                'weekly_online_offline_price' => $request->input('weekly_online_offline_price'),
            ]);

            $game->genres()->attach($request->input('genres'));
            $game->platforms()->attach($request->input('platforms'));

            DB::commit();

            return $this->success('Game created successfully', new GameDetailResource($game), 201);

        } catch (\Exception $e) {
            DB::rollBack();

            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
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
        $changed = $request->safe()->only([
            'title',
            'description',
            'release_date',
            'age_rating',
            'weekly_online_price',
            'weekly_online_offline_price'
        ]);

        if ($request->filled('title')) {
            $changed['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('image')) {
            if (Storage::disk('public')->exists($game->image_url)) {
                Storage::disk('public')->delete($game->image_url);
            }

            $gameSlug = $changed['slug'] ?? $game->slug;

            $changed['image_url'] = $request->file('image')->store("images/games/{$gameSlug}", 'public');
        }

        if ($request->filled('genres')) {
            $game->genres()->sync($request->input('genres'));
        }

        if ($request->filled('platforms')) {
            $game->platforms()->sync($request->input('platforms'));
        }

        $game->update($changed);

        return $this->success('Game updated successfully', new GameDetailResource($game));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        //
    }
}
