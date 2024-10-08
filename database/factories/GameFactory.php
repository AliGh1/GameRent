<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph,
            'slug' => $this->faker->unique()->slug(),
            'image_url' => $this->faker->imageUrl(),
            'weekly_online_price' => $this->faker->numberBetween(50000, 150000),
            'weekly_online_offline_price' => $this->faker->numberBetween(60000, 200000),
            'release_date' => $this->faker->date(),
            'age_rating' => $this->faker->randomElement(['PEGI 3', 'PEGI 7', 'PEGI 12', 'PEGI 16', 'PEGI 18']),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Game $game): void {
            $genres = Genre::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $game->genres()->sync($genres);

            $platforms = Platform::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $game->platforms()->sync($platforms);
        });
    }
}
