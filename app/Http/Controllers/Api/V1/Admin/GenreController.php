<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GenreRequest;
use App\Models\Genre;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Gate;

class GenreController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('view.genre');

        return $this->success('Genres retrieved successfully', Genre::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GenreRequest $request)
    {
        Gate::authorize('create.genre');

        $genre = Genre::create($request->validated());

        return $this->success('Genre created Successfully', $genre, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GenreRequest $request, Genre $genre)
    {
        Gate::authorize('edit.genre');

        $genre->update($request->validated());

        return $this->success('Genre Updated Successfully', $genre);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        Gate::authorize('delete.genre');

        $genre->delete();

        return $this->ok('Genre deleted Successfully');
    }
}
