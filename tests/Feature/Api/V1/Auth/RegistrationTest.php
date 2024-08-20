<?php

namespace Tests\Feature\Api\V1\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    public function test_new_users_can_register(): void
    {
        Event::fake();

        $response = $this->post('api/v1/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertOk();

        Event::assertDispatched(Registered::class, function ($event)  {
            return $event->user->email === 'test@example.com';
        });

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $response->assertExactJson([
            'data' => [
                'token' => $response->json('data.token'),
            ],
            'message' => 'Authenticated',
            'status' => 200,
        ]);
    }

    // TODO Authenticated user shouldn't be able to register
    // TODO Check Validation for Register Request

    // Sanctum::actingAs(
    //      User::factory()->create()
    // );
}
