<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AccountMode;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_permission_can_create_account(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'create.account']);
        $user->givePermissionTo($permission);

        $game = Game::factory()->create();

        Sanctum::actingAs($user);

        $data = [
            'email' => 'game@example.com',
            'password' => 'password',
            'secret_key' => 'DJHJMGSSCMJ5XNMR',
            'mode' => AccountMode::OnlineOffline,
        ];

        $response = $this->postJson("api/v1/admin/games/$game->id/accounts", $data);

        $response->assertCreated();

        $response->assertExactJson([
            'message' => 'Account created successfully',
            'status' => 201,
        ]);

        $this->assertDatabaseHas('accounts', [
            'email' => $data['email'],
            'mode' => $data['mode'],
        ]);
    }
}
