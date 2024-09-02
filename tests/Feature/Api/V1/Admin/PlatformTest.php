<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Platform;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_permission_can_create_platform(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'create.platform']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->postJson('api/v1/admin/platforms', [
            'name' => 'Test Platform',
        ]);

        $response->assertCreated();

        $response->assertExactJson([
            'message' => 'Platform created Successfully',
            'data' => [
                'id' => 1,
                'name' => 'Test Platform'
            ],
            'status' => 201,
        ]);

        $this->assertDatabaseHas('platforms', [
            'name' => 'Test Platform',
        ]);
    }
    public function test_admin_without_permission_can_not_create_platform(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('api/v1/admin/platforms', [
            'name' => 'Test Platform',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_with_permission_can_view_platforms(): void
    {
        $user = User::factory()->create();
        $platforms = Platform::factory(3)->create();
        $permission = Permission::create(['name' => 'view.platforms']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->getJson('api/v1/admin/platforms');

        $response->assertJson($platforms->map(function ($platform) {
            return [
                'id' => $platform->id,
                'name' => $platform->name,
            ];
        })->toArray());
    }
}
