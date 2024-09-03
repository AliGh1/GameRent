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

    public function test_admin_with_permission_can_view_platforms(): void
    {
        $user = User::factory()->create();
        $platforms = Platform::factory(3)->create();
        $permission = Permission::create(['name' => 'view.platform']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->getJson('api/v1/admin/platforms');

        $response->assertOk();

        $response->assertJson($platforms->map(function ($platform) {
            return [
                'id' => $platform->id,
                'name' => $platform->name,
            ];
        })->toArray());
    }

    public function test_admin_with_permission_can_edit_platform(): void
    {
        $user = User::factory()->create();
        $platform = Platform::factory()->create();
        $permission = Permission::create(['name' => 'edit.platform']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->putJson("api/v1/admin/platforms/{$platform->id}", [
            'name' => 'Changed Platform Name',
        ]);

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Platform Updated Successfully',
            'data' => [
                'id' => $platform->id,
                'name' => 'Changed Platform Name'
            ],
            'status' => 200,
        ]);

        $platform = $platform->fresh();
        $this->assertEquals('Changed Platform Name', $platform->name);
    }

    public function test_admin_without_permissions_cannot_manage_platform(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $platform = Platform::factory()->create();

        $response = $this->postJson('api/v1/admin/platforms', [
            'name' => 'Test Platform',
        ]);
        $response->assertForbidden();

        $response = $this->getJson('api/v1/admin/platforms');
        $response->assertForbidden();

        $response = $this->putJson("api/v1/admin/platforms/{$platform->id}", [
            'name' => 'Changed Platform Name',
        ]);
        $response->assertForbidden();
    }
}
