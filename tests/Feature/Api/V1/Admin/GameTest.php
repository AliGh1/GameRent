<?php

namespace Api\V1\Admin;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_permission_can_create_game(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'create.game']);
        $user->givePermissionTo($permission);

        $genres = Genre::factory()->count(2)->create();
        $platforms = Platform::factory()->count(2)->create();

        Sanctum::actingAs($user);

        Storage::fake('public');
        $image = UploadedFile::fake()->image('game.jpg');

        $data = [
            'title' => 'New Game',
            'description' => 'A detailed description of the new game.',
            'release_date' => '2024-01-01',
            'age_rating' => 'PEGI 16',
            'image' => $image,
            'genres' => $genres->pluck('id')->toArray(),
            'platforms' => $platforms->pluck('id')->toArray(),
            'weekly_online_price' => 90000,
            'weekly_online_offline_price' => 120000,
        ];

        $response = $this->postJson('api/v1/admin/games', $data);

        $response->assertCreated();

        $expected = [
            'data' => [
                'id' => 1,
                'title' => $data['title'],
                'description' => $data['description'],
                'image_url' => "images/games/new-game/{$image->hashName()}",
                'release_date' => $data['release_date'],
                'age_rating' => $data['age_rating'],
                'genres' => $genres->pluck('name')->toArray(),
                'platforms' => $platforms->pluck('name')->toArray(),
                'price' => [
                    'online' => [
                        'one_week' => 90000,
                        'two_week' => 162000,
                        'three_week' => 229500,
                        'one_month' => 288000,
                    ],
                    'online_offline' => [
                        'one_week' => 120000,
                        'two_week' => 216000,
                        'three_week' => 306000,
                        'one_month' => 384000,
                    ],
                ],
                'slug' => 'new-game',
            ],
            'message' => 'Game created successfully',
            'status' => 201,
        ];

        $response->assertJson($expected);

        $this->assertDatabaseHas('games', [
            'id' => 1,
            'title' => $data['title'],
        ]);

        Storage::disk('public')->assertExists("images/games/new-game/{$image->hashName()}");
    }

    public function test_admin_without_permission_can_not_create_game(): void
    {
        $user = User::factory()->create();

        $genres = Genre::factory()->count(2)->create();
        $platforms = Platform::factory()->count(2)->create();

        Sanctum::actingAs($user);

        Storage::fake('public');
        $image = UploadedFile::fake()->image('game.jpg');

        $data = [
            'title' => 'New Game',
            'description' => 'A detailed description of the new game.',
            'release_date' => '2024-01-01',
            'age_rating' => 'PEGI 16',
            'image' => $image,
            'genres' => $genres->pluck('id')->toArray(),
            'platforms' => $platforms->pluck('id')->toArray(),
            'weekly_online_price' => 90000,
            'weekly_online_offline_price' => 120000,
        ];

        $response = $this->postJson('api/v1/admin/games', $data);

        $response->assertForbidden();

        $response->assertExactJson([
            'message' => 'Forbidden'
        ]);

        $this->assertDatabaseMissing('games', [
            'id' => 1,
            'title' => $data['title'],
        ]);

        Storage::disk('public')->assertMissing("images/games/new-game/{$image->hashName()}");
    }

    public function test_admin_with_permission_can_update_game(): void
    {
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'edit.game']);
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        Storage::fake('public');

        // Before
        $game = Game::factory()->create([
            'title' => 'Old Game',
            'slug' => 'old-game',
            'image_url' => 'images/games/old-game/old-image.jpg'
        ]);

        Storage::disk('public')->put('images/games/old-game/old-image.jpg', '');

        $genres = Genre::factory()->count(2)->create();
        $platforms = Platform::factory()->count(2)->create();

        $game->genres()->sync($genres);
        $game->platforms()->sync($platforms);

        // After
        $newGenres = Genre::factory()->count(2)->create();
        $newPlatforms = Platform::factory()->count(2)->create();

        $image = UploadedFile::fake()->image('new-game.jpg');

        $data = [
            'title' => 'Updated Game',
            'description' => 'An updated description of the game.',
            'release_date' => '2024-02-01',
            'age_rating' => 'PEGI 18',
            'image' => $image,
            'genres' => $newGenres->pluck('id')->toArray(),
            'platforms' => $newPlatforms->pluck('id')->toArray(),
            'weekly_online_price' => 90000,
            'weekly_online_offline_price' => 120000,
        ];

        $response = $this->putJson("api/v1/admin/games/{$game->id}", $data);

        $response->assertOk();

        $expected = [
            'data' => [
                'id' => $game->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'image_url' => "images/games/updated-game/{$image->hashName()}",
                'release_date' => $data['release_date'],
                'age_rating' => $data['age_rating'],
                'genres' => $newGenres->pluck('name')->toArray(),
                'platforms' => $newPlatforms->pluck('name')->toArray(),
                'price' => [
                    'online' => [
                        'one_week' => 90000,
                        'two_week' => 162000,
                        'three_week' => 229500,
                        'one_month' => 288000,
                    ],
                    'online_offline' => [
                        'one_week' => 120000,
                        'two_week' => 216000,
                        'three_week' => 306000,
                        'one_month' => 384000,
                    ],
                ],
                'slug' => 'updated-game',
            ],
            'message' => 'Game updated successfully',
            'status' => 200,
        ];

        $response->assertJson($expected);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'title' => $data['title'],
        ]);

        $this->assertDatabaseMissing('game_genre', [
            'game_id' => $game->id,
            'genre_id' => $genres->first()->id,
        ]);
        $this->assertDatabaseMissing('game_platform', [
            'game_id' => $game->id,
            'platform_id' => $platforms->first()->id,
        ]);

        $this->assertDatabaseHas('game_genre', [
            'game_id' => $game->id,
            'genre_id' => $newGenres->first()->id,
        ]);
        $this->assertDatabaseHas('game_platform', [
            'game_id' => $game->id,
            'platform_id' => $newPlatforms->first()->id,
        ]);

        Storage::disk('public')->assertExists("images/games/updated-game/{$image->hashName()}");
        Storage::disk('public')->assertMissing('images/games/old-game/old-image.jpg');
    }

    public function test_admin_without_permission_cannot_update_game(): void
    {
        $user = User::factory()->create();


        $genres = Genre::factory()->count(2)->create();
        $platforms = Platform::factory()->count(2)->create();

        $game = Game::factory()->create([
            'image_url' => 'images/games/old-game/old-image.jpg'
        ]);

        Storage::fake('public');

        Storage::disk('public')->put('images/games/old-game/old-image.jpg', '');

        $game->genres()->sync($genres);
        $game->platforms()->sync($platforms);

        // Set up new data for the update
        $newGenres = Genre::factory()->count(2)->create();
        $newPlatforms = Platform::factory()->count(2)->create();
        $image = UploadedFile::fake()->image('new-game.jpg');

        $data = [
            'title' => 'Updated Game',
            'description' => 'An updated description of the game.',
            'release_date' => '2024-02-01',
            'age_rating' => 'PEGI 18',
            'image' => $image,
            'genres' => $newGenres->pluck('id')->toArray(),
            'platforms' => $newPlatforms->pluck('id')->toArray(),
            'weekly_online_price' => 90000,
            'weekly_online_offline_price' => 120000,
        ];

        Sanctum::actingAs($user);

        $response = $this->putJson("api/v1/admin/games/{$game->id}", $data);

        $response->assertForbidden();

        $game->refresh();

        $this->assertDatabaseMissing('games', [
            'id' => $game->id,
            'title' => $data['title'],
        ]);

        $this->assertDatabaseHas('games', ['image_url' => 'images/games/old-game/old-image.jpg']);
    }
}
