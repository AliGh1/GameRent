<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponses;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken('API token for ' . $user->email)->plainTextToken
            ]
        );
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $request->authenticate();

        $tokenExpiration = $request->boolean('remember') ? now()->addWeek() : null;

        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken('API token for ' . $user->email, expiresAt: $tokenExpiration)->plainTextToken
            ]
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Logged out successfully');
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->ok('Logged out from all devices successfully');
    }
}
