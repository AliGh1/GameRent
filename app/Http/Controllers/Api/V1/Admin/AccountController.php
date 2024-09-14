<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Account\StoreAccountRequest;
use App\Models\Account;
use App\Models\Game;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Request;


class AccountController extends Controller
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
    public function store(StoreAccountRequest $request, Game $game)
    {
        $game->accounts()->create([
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'secret_key' => bcrypt($request->input('secret_key')),
            'mode' => $request->input('mode'),
        ]);

        return $this->success('Account created successfully', [], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game, Account $account)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {

    }
}
