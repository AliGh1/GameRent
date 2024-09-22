<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'rental_id' => Rental::factory(),
            'amount' => $this->faker->numberBetween(1000, 5000),
            'status' => PaymentStatus::PENDING,
            'transaction_id' => $this->faker->uuid,
            'gateway' => $this->faker->word,
        ];
    }
}
