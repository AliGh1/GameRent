<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PlatformRequest;
use App\Models\Platform;
use App\Traits\ApiResponses;
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

        return $this->success('Platforms retrieved successfully', Platform::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlatformRequest $request)
    {
        Gate::authorize('create.platform');

        $platform = Platform::create($request->validated());

        return $this->success('Platform created Successfully', $platform, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlatformRequest $request, Platform $platform)
    {
        Gate::authorize('edit.platform');

        $platform->update($request->validated());

        return $this->success('Platform Updated Successfully', $platform);
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
