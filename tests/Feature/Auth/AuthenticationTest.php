<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertNoContent();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertNoContent();
    }

    public function test_users_can_logout_other_devices(): void
    {
        $user = User::factory()->create();

        $user->createToken('Token 1');
        $user->createToken('Token 2');

        $this->assertEquals(2, $user->tokens()->count());

        $this->actingAs($user);

        $response = $this->postJson('/logout-other-devices', [
            'password' => 'password'
        ]);

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Logged out from other devices successfully',
            'status' => 200,
        ]);

        $this->assertEquals(0, $user->tokens()->count());
    }
}
