<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Account\StoreAccountRequest;
use App\Http\Requests\Api\V1\Account\UpdateAccountRequest;
use App\Models\Account;
use App\Models\Game;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Gate;
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
            'password' => encrypt($request->input('password')),
            'secret_key' => encrypt($request->input('secret_key')),
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
    public function update(UpdateAccountRequest $request, Game $game, Account $account)
    {
        $changed = $request->safe()->only([
            'email',
            'mode',
        ]);

        if ($request->filled('password')) {
            $changed['password'] = encrypt($request->input('password'));
        }

        if ($request->filled('secret_key')) {
            $changed['secret_key'] = encrypt($request->input('secret_key'));
        }

        $account->update($changed);

        return $this->ok('Account updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game, Account $account)
    {
        Gate::authorize('delete.account');

        $account->delete();

        return $this->ok('Account deleted successfully');
    }
}
