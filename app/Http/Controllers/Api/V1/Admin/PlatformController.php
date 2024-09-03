<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlatformController extends Controller
{
    use ApiResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('view.platform');

        return Platform::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create.platform');

        $request->validate([
            'name' => 'required|string|max:50|unique:platforms',
        ]);

        $platform = Platform::create([
            'name' => $request->name,
        ]);

        return $this->success('Platform created Successfully', [
            'id' => $platform->id,
            'name' => $platform->name,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Platform $platform)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Platform $platform)
    {
        Gate::authorize('edit.platform');

        $request->validate([
            'name' => 'required|string|max:50|unique:platforms',
        ]);

        $platform->update([
            'name' => $request->name
        ]);

        return $this->success('Platform Updated Successfully', [
            'id' => $platform->id,
            'name' => $platform->name,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Platform $platform)
    {
        Gate::authorize('delete.platform');

        $platform->delete();

        return $this->ok('Platform deleted Successfully');
    }
}
