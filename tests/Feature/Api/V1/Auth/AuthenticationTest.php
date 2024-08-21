<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();

        $response->assertExactJson([
            'data' => [
                'token' => $response->json('data.token'),
            ],
            'message' => 'Authenticated',
            'status' => 200,
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('api/v1/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertEquals(0, $user->tokens()->count());

        $response->assertExactJson([
            'message' => 'These credentials do not match our records.',
            'errors' => [
                'email' => ['These credentials do not match our records.']
            ]
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/v1/logout');

        $response->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => explode('|', $token)[0],
        ]);

        $response->assertExactJson([
            'data' => [],
            'message' => 'Logged out successfully',
            'status' => 200,
        ]);
    }

    public function test_users_can_logout_from_everywhere(): void
    {
        $user = User::factory()->create();

        $user->createToken('Token1');
        $token = $user->createToken('Token2')->plainTextToken;

        $this->assertEquals(2, $user->tokens()->count());

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/v1/logout-all');

        $response->assertOk();

        $this->assertEquals(0, $user->tokens()->count());

        $response->assertExactJson([
            'data' => [],
            'message' => 'Logged out from all devices successfully',
            'status' => 200,
        ]);
    }

    // TODO Authenticated user shouldn't be able to login
    // TODO Check Validation for Login Request
}
