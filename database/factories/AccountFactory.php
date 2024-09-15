<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Crypt::encryptString('password'),
            'secret_key' => encrypt('DJHJMGSSCMJ5XNMR'), // an example of real secret key
            'mode' => $this->faker->randomElement(['online', 'online_offline'])
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => false,
        ]);
    }
}
