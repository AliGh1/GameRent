<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rental>
 */
class RentalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'rental_date' => $this->faker->dateTimeBetween('-2 weeks', '+1 week'),
            'return_date' => $this->faker->dateTimeBetween('+1 weeks', '+1 month'),
            'status' => $this->faker->randomElement([
                'pending',
                'active',
                'expired',
                'returned',
                'suspended',
                'canceled'
            ])
        ];
    }
}
