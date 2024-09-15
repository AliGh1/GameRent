<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Enums\AccountMode;
use App\Models\Account;
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

    public function test_admin_with_permission_can_update_account(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'edit.account']);
        $user->givePermissionTo($permission);

        $account = Account::factory()->create();

        Sanctum::actingAs($user);

        $data = [
            'email' => 'game@example.com',
            'password' => 'password',
            'secret_key' => 'DJHJMGSSCMJ5XNMR',
            'mode' => AccountMode::OnlineOffline,
        ];

        $response = $this->putJson("api/v1/admin/games/$account->game_id/accounts/$account->id", $data);

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Account updated successfully',
            'status' => 200,
        ]);

        $this->assertDatabaseHas('accounts', [
            'email' => $data['email'],
            'mode' => $data['mode'],
        ]);

        $this->assertDatabaseMissing('accounts', [
            'email' => $account->email,
            'mode' => $account->mode,
        ]);
    }

    public function test_admin_with_permission_can_destroy_account(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'delete.account']);
        $user->givePermissionTo($permission);

        $account = Account::factory()->create();

        Sanctum::actingAs($user);


        $response = $this->deleteJson("api/v1/admin/games/$account->game_id/accounts/$account->id");

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Account deleted successfully',
            'status' => 200,
        ]);

        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
        ]);
    }

    public function test_admin_without_permissions_cannot_manage_account(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $account = Account::factory()->create();
        $game = Game::factory()->create();

        $data = [
            'email' => 'game@example.com',
            'password' => 'password',
            'secret_key' => 'DJHJMGSSCMJ5XNMR',
            'mode' => AccountMode::OnlineOffline,
        ];

        $response = $this->postJson("api/v1/admin/games/$game->id/accounts", $data);
        $response->assertForbidden();

        $response = $this->putJson("api/v1/admin/games/$account->game_id/accounts/$account->id", $data);
        $response->assertForbidden();

        $response = $this->deleteJson("api/v1/admin/games/$account->game_id/accounts/$account->id");
        $response->assertForbidden();
    }
}
