<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AccountMode;
use App\Enums\PaymentStatus;
use App\Enums\RentalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreRentalRequest;
use App\Models\Account;
use App\Models\Game;
use App\Models\Payment;
use App\Models\Rental;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment as ShetabitPayment;

class RentalController extends Controller
{
    use ApiResponses;

    private const ONE_MONTH = 4;

    public function store(StoreRentalRequest $request, Game $game)
    {
        $account = $game->getAvailableAccountByMode(AccountMode::from($request->account_mode));

        if (!$account) {
            return $this->error('No available accounts for this game with the selected criteria.', 404);
        }

        $price = $game->calculatePrice($request->rental_duration_weeks, AccountMode::from($request->account_mode));

        try {
            DB::beginTransaction();

            $invoice = $this->createInvoice($price);

            $paymentResponse = ShetabitPayment::callbackUrl(route('api.payment.callback'))
                ->purchase($invoice)
                ->pay()
                ->toJson();

            $returnDate = $this->calculateReturnDate($request->rental_duration_weeks);
            $rental = $this->createRental($account, $returnDate);

            $account->update(['availability' => false]);

            $this->createPayment($rental, $invoice);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage(), 422);
        }

        // Decode the payment URL from the response
        $paymentUrl = json_decode($paymentResponse, true)['action'];

        return $this->success('Rental request created. Please complete the payment', ['payment_url' => $paymentUrl]);
    }

    /**
     * Handle payment callback
     */
    public function callback(Request $request)
    {
        try {
            $payment = Payment::where('transaction_id', $request->token)->firstOrFail();

            $receipt = ShetabitPayment::amount($payment->amount)->transactionId($request->token)->verify();

            $payment->update([
                'status' => PaymentStatus::PAID
            ]);

            $payment->rental()->update([
                'status' => RentalStatus::ACTIVE
            ]);

            Log::info('Payment verified successfully', ['reference_id' => $receipt->getReferenceId()]);

            return redirect()->to(config('app.frontend_url') . '/payment-status?status=success&reference_id=' . urlencode($receipt->getReferenceId()));

        } catch (Exception $e) {
            return $this->handlePaymentFailure($request->token, $e->getMessage());
        }
    }

    /**
     * Handle payment failure by reverting changes.
     *
     * @param string $transactionId
     * @param string $message
     * @return RedirectResponse
     */
    private function handlePaymentFailure(string $transactionId, string $message): RedirectResponse
    {
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if ($payment) {
            $payment->update([
                'status' => PaymentStatus::FAILED
            ]);

            $rental = $payment->rental;
            if ($rental) {
                $rental->update([
                    'status' => RentalStatus::CANCELED
                ]);

                $account = $rental->account;
                if ($account) {
                    $account->update([
                        'availability' => true
                    ]);
                }
            }

            Log::error('Payment failed', ['transaction_id' => $transactionId, 'message' => $message]);
        }

        return redirect()->to(config('app.frontend_url') . '/payment-status?status=error&message=' . urlencode($message));
    }



    /**
     * Calculate return date based on rental duration
     */
    private function calculateReturnDate(int $rentalDurationWeeks): Carbon
    {
        return $rentalDurationWeeks === self::ONE_MONTH
            ? now()->addMonth()
            : now()->addWeeks($rentalDurationWeeks);
    }

    /**
     * Create a rental record
     */
    private function createRental(Account $account, Carbon $returnDate): Rental
    {
        return $account->rentals()->create([
            'user_id' => auth()->id(),
            'rental_date' => now(),
            'return_date' => $returnDate,
            'status' => RentalStatus::PENDING
        ]);
    }

    /**
     * Create a payment record
     */
    private function createPayment(Rental $rental, Invoice $invoice): void
    {
        $rental->payments()->create([
            'user_id' => auth()->id(),
            'amount' => $invoice->getAmount(),
            'status' => PaymentStatus::PENDING,
            'transaction_id' => $invoice->getTransactionId(),
            'gateway' => $invoice->getDriver(),
        ]);
    }

    /**
     * Create an invoice for payment
     * @throws Exception
     */
    private function createInvoice(int $price): Invoice
    {
        $invoice = new Invoice();
        $invoice->amount($price)->via(config('payment.default'));
        return $invoice;
    }
}
