<?php

namespace Api\V1;

use App\Enums\AccountMode;
use App\Enums\PaymentStatus;
use App\Enums\RentalStatus;
use App\Models\Account;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Shetabit\Payment\Facade\Payment as ShetabitPayment;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RentalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_rent_game_if_account_available(): void
    {
        // Arrange
        $game = Game::factory()->create();
        $account = Account::factory()->create([
            'game_id' => $game->id,
            'mode' => AccountMode::ONLINE_OFFLINE,
            'availability' => true,
        ]);
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $rentalPeriod = 2; // 2 weeks
        $amount = $game->calculatePrice($rentalPeriod, AccountMode::ONLINE_OFFLINE);

        // Act
        $response = $this->postJson("api/v1/rentals/$game->id", [
            'account_mode' => AccountMode::ONLINE_OFFLINE,
            'rental_duration_weeks' => $rentalPeriod
        ]);

        // Assert
        $response->assertOk();
        $response->assertJson([
            'message' => 'Rental request created. Please complete the payment'
        ]);

        $response->assertJsonStructure([
            'data' => [
                'payment_url'
            ],
            'status',
            'message'
        ]);

        // Check the rental was created
        $this->assertDatabaseHas('rentals', [
            'user_id' => $user->id,
            'account_id' => $account->id,
            'rental_date' => now()->toDateTimeString(),
            'return_date' => now()->addWeeks($rentalPeriod)->toDateTimeString(),
            'status' => RentalStatus::PENDING,
        ]);

        // Check the account is marked as unavailable
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'availability' => false,
        ]);

        // Check the payment record was created
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'rental_id' => 1,
            'amount' => $amount,
            'status' => PaymentStatus::PENDING,
        ]);
    }

    public function test_rental_creation_rolls_back_on_payment_failure(): void
    {
        // Arrange
        $game = Game::factory()->create();
        $account = Account::factory()->create([
            'game_id' => $game->id,
            'mode' => AccountMode::ONLINE_OFFLINE,
            'availability' => true,
        ]);
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $rentalPeriod = 2; // 2 weeks
        $amount = $game->calculatePrice($rentalPeriod, AccountMode::ONLINE_OFFLINE);

        // Mock the payment gateway to throw an exception
        ShetabitPayment::shouldReceive('callbackUrl->purchase->pay->toJson')
            ->andThrow(new \Exception('Payment failed'));

        // Act
        $response = $this->postJson("api/v1/rentals/$game->id", [
            'account_mode' => AccountMode::ONLINE_OFFLINE,
            'rental_duration_weeks' => $rentalPeriod
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Payment failed'
        ]);

        // Check the rental and payment were NOT created
        $this->assertDatabaseMissing('rentals', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('payments', [
            'user_id' => $user->id,
        ]);

        // Check the account availability is not changed
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'availability' => true,
        ]);
    }
}
