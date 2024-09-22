<?php

namespace Tests\Feature\Api\V1\Game;

use App\Enums\AccountMode;
use App\Models\Account;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_list_all_games(): void
    {
        Game::factory()->count(10)->create();

        $response = $this->get('api/v1/games');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'slug',
                    'image_url',
                    'weekly_online_price',
                    'weekly_online_offline_price',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links' => [
                    '*' => [
                        'url',
                        'label',
                        'active',
                    ],
                ],
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);

        $response->assertJsonFragment([
            'current_page' => 1,
            'per_page' => 15,
            'total' => 10,
        ]);

        $response->assertJsonCount(10, 'data');
    }

    public function test_it_can_show_a_specific_game()
    {
        $game = Game::factory()
            ->has(Genre::factory()->count(2))
            ->has(Platform::factory()->count(2))
            ->create();

        Account::factory()->create([
            'game_id' => $game->id,
            'mode' => 'online',
        ]);

        Account::factory()->unavailable()->create([
            'game_id' => $game->id,
            'mode' => 'online_offline',
        ]);

        $response = $this->getJson("api/v1/games/{$game->id}");

        $response->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $game->id,
                'title' => $game->title,
                'description' => $game->description,
                'slug' => $game->slug,
                'image_url' => $game->image_url,
                'release_date' => $game->release_date,
                'age_rating' => $game->age_rating,
                'genres' => $game->genres->pluck('name')->toArray(),
                'platforms' => $game->platforms->pluck('name')->toArray(),
                'availability' => [
                    'online' => true,
                    'online_offline' => false,
                ],
                'price' => [
                    'online' => [
                        'one_week' => $game->calculatePrice(1, AccountMode::ONLINE),
                        'two_week' => $game->calculatePrice(2, AccountMode::ONLINE),
                        'three_week' => $game->calculatePrice(3, AccountMode::ONLINE),
                        'one_month' => $game->calculatePrice(4, AccountMode::ONLINE),
                    ],
                    'online_offline' => [
                        'one_week' => $game->calculatePrice(1, AccountMode::ONLINE_OFFLINE),
                        'two_week' => $game->calculatePrice(2, AccountMode::ONLINE_OFFLINE),
                        'three_week' => $game->calculatePrice(3, AccountMode::ONLINE_OFFLINE),
                        'one_month' => $game->calculatePrice(4, AccountMode::ONLINE_OFFLINE),
                    ],
                ],
            ],
        ]);
    }


    public function test_it_returns_404_if_game_not_found()
    {
        $response = $this->getJson('/api/games/999');

        $response->assertNotFound();

        $response->assertJson(['message' => 'Not Found']);
    }
}
