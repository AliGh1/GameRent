<?php

namespace Api\V1;

use App\Enums\PaymentStatus;
use App\Enums\RentalStatus;
use App\Models\Payment;
use App\Models\Rental;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Shetabit\Payment\Facade\Payment as ShetabitPayment;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Exceptions\InvoiceNotFoundException;
use Shetabit\Multipay\Exceptions\PurchaseFailedException;
use Shetabit\Multipay\Exceptions\TimeoutException;
use Tests\TestCase;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    private Rental $rental;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Common setup for the tests
        $this->rental = Rental::factory()->create([
            'status' => RentalStatus::PENDING,
        ]);

        $this->payment = Payment::factory()->create([
            'rental_id' => $this->rental->id,
            'status' => PaymentStatus::PENDING,
            'transaction_id' => 'dummy-transaction-id',
            'amount' => 1000,
        ]);
    }

    public function test_successful_payment_callback()
    {
        // Mock payment verification
        $receipt = Mockery::mock(ReceiptInterface::class);
        $receipt->shouldReceive('getReferenceId')->andReturn('dummy-reference-id');

        ShetabitPayment::shouldReceive('amount')
            ->with(1000)
            ->andReturnSelf();
        ShetabitPayment::shouldReceive('transactionId')
            ->with('dummy-transaction-id')
            ->andReturnSelf();
        ShetabitPayment::shouldReceive('verify')
            ->andReturn($receipt);

        // Act
        $response = $this->getJson("/api/v1/payment/callback?status=1&token=dummy-transaction-id");

        // Assert
        $response->assertRedirect(config('app.frontend_url') . '/payment-status?status=success&reference_id=dummy-reference-id');

        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'dummy-transaction-id',
            'status' => PaymentStatus::PAID
        ]);

        $this->assertDatabaseHas('rentals', [
            'id' => $this->rental->id,
            'status' => RentalStatus::ACTIVE
        ]);
    }

    #[DataProvider('failedPaymentProvider')]
    public function test_failed_payment_callbacks($exception, $message)
    {
        // Mock failure scenarios
        ShetabitPayment::shouldReceive('amount')->andReturnSelf();
        ShetabitPayment::shouldReceive('transactionId')->andReturnSelf();
        ShetabitPayment::shouldReceive('verify')->andThrow($exception);

        // Act
        $response = $this->get("/api/v1/payment/callback?status=0&token=dummy-transaction-id");

        // Assert
        $response->assertRedirect(config('app.frontend_url') . "/payment-status?status=error&message={$message}");

        $this->assertDatabaseHas('payments', [
            'transaction_id' => 'dummy-transaction-id',
            'status' => PaymentStatus::FAILED
        ]);

        $this->assertDatabaseHas('rentals', [
            'id' => $this->rental->id,
            'status' => RentalStatus::CANCELED
        ]);
    }

    public static function failedPaymentProvider(): array
    {
        return [
            [new InvoiceNotFoundException('Invoice not found.'), 'Invoice+not+found.'],
            [new TimeoutException('Payment verification timed out.'), 'Payment+verification+timed+out.'],
            [new PurchaseFailedException('Purchase verification failed.'), 'Purchase+verification+failed.'],
            [new Exception('Generic error.'), 'Generic+error.'],
        ];
    }
}
