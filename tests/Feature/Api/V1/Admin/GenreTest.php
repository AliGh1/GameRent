<?php

namespace Api\V1\Admin;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_permission_can_create_genre(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'create.genre']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->postJson('api/v1/admin/genres', [
            'name' => 'Test Genre',
        ]);

        $response->assertCreated();

        $response->assertExactJson([
            'message' => 'Genre created Successfully',
            'data' => [
                'id' => 1,
                'name' => 'Test Genre'
            ],
            'status' => 201,
        ]);

        $this->assertDatabaseHas('genres', [
            'name' => 'Test Genre',
        ]);
    }

    public function test_admin_with_permission_can_view_genres(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory(3)->create();
        $permission = Permission::create(['name' => 'view.genre']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->getJson('api/v1/admin/genres');

        $response->assertOk();

        $expectedData = [
            'message' => 'Genres retrieved successfully',
            'data' => $genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ];
            })->toArray(),
            'status' => 200
        ];

        $response->assertExactJson($expectedData);
    }

    public function test_admin_with_permission_can_edit_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $permission = Permission::create(['name' => 'edit.genre']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->putJson("api/v1/admin/genres/{$genre->id}", [
            'name' => 'Changed Genre Name',
        ]);

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Genre Updated Successfully',
            'data' => [
                'id' => $genre->id,
                'name' => 'Changed Genre Name'
            ],
            'status' => 200,
        ]);

        $genre = $genre->fresh();
        $this->assertEquals('Changed Genre Name', $genre->name);
    }

    public function test_admin_with_permission_can_delete_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $permission = Permission::create(['name' => 'delete.genre']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("api/v1/admin/genres/{$genre->id}");

        $response->assertOk();

        $response->assertExactJson([
            'message' => 'Genre deleted Successfully',
            'status' => 200,
        ]);

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_admin_without_permissions_cannot_manage_genre(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $genre = Genre::factory()->create();

        $response = $this->postJson('api/v1/admin/genres', [
            'name' => 'Test Genre',
        ]);
        $response->assertForbidden();

        $response = $this->getJson('api/v1/admin/genres');
        $response->assertForbidden();

        $response = $this->putJson("api/v1/admin/genres/{$genre->id}", [
            'name' => 'Changed Genre Name',
        ]);
        $response->assertForbidden();

        $response = $this->deleteJson("api/v1/admin/genres/{$genre->id}");
        $response->assertForbidden();
    }
}
