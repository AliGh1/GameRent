<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->assertGuest();

        $response->assertExactJson([
            'message' => 'These credentials do not match our records.',
            'errors' => [
                'email' => ['These credentials do not match our records.']
            ]
        ]);
    }

    // TODO Authenticated user shouldn't be able to login
    // TODO Check Validation for Login Request
}
