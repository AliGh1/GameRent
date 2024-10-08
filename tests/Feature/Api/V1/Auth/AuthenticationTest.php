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

    public function test_authenticated_user_cannot_login_again(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->post('api/v1/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $response->assertForbidden();
        $response->assertJson(['message' => 'You are already authenticated.']);
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
            'message' => 'Logged out successfully',
            'status' => 200,
        ]);
    }

    public function test_users_can_logout_other_devices(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('Token 1')->plainTextToken;

        $user->createToken('Token 2');

        $this->assertEquals(2, $user->tokens()->count());

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('api/v1/logout-other-devices', [
                'password' => 'password'
            ]);

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Logged out from other devices successfully',
            'status' => 200,
        ]);

        $this->assertEquals(1, $user->tokens()->count());

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Token 1',
        ]);
    }

    public function test_login_validation(): void
    {
        $response = $this->postJson('api/v1/login');
        $response->assertUnprocessable();
        $response->assertExactJson([
            'message' => 'The email field is required. (and 1 more error)',
            'errors' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ]);
    }
}
