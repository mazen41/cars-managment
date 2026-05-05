<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentProviderInterface;
use App\Contracts\Payment\PaymentResponse;
use App\DTOs\Payment\PaymentRequest;
use App\Events\CarInspectionPaid;
use App\Models\CarInspection;
use App\Models\CarReservation;
use App\Models\User;
use App\Models\CombinedOrder;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Enums\PaymentType;
use App\Enums\PaymentStatusEnum;
use App\Events\CarReservationPaid;
use App\Utility\NotificationUtility;
use Exception;

class PaymentService
{
    private PaymentFactory $paymentFactory;

    public function __construct(PaymentFactory $paymentFactory)
    {
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * Process payment
     */
    public function processPayment(PaymentRequest $paymentRequest): array
    {
        try {
            // Validate request
            $validationErrors = $paymentRequest->validate();
            if (!empty($validationErrors)) {
                return $this->errorResponse(implode(', ', $validationErrors));
            }

            // Validate user and phone number
            $user = User::find($paymentRequest->userId);
            if (!$user) {
                return $this->errorResponse('User not found');
            }

            if (empty($user->phone)) {
                return $this->errorResponse(translate('Please add phone number to your profile'));
            }

            $paymentRequest->userPhone = $user->phone;

            // Validate wallet payment setting
            if ($paymentRequest->isWalletPayment() && !get_setting('recharge_wallet_active')) {
                return $this->errorResponse(translate('Recharging credit is not available now'));
            }
            //
            if($paymentRequest->isWalletPayment() && $paymentRequest->provider == 'wallet'){
                return $this->errorResponse(translate('You cannot use wallet payment for wallet recharge'));
            }

            // Test the process
            if(get_setting('test_payment')){
                if ($paymentRequest->provider === 'test') {
                    // Simulate a successful payment
                    $paymentResponse = PaymentResponse::success('Test payment successful', 'TEST_TXN_12345', 'TEST_REF_12345');

                    // Handle successful payment
                    $this->handleSuccessfulPayment($paymentRequest, $paymentResponse);

                    return $this->successResponse(translate('Test payment processed successfully'));
                }
            }

            // Get payment provider
            $provider = $this->paymentFactory->createProvider($paymentRequest->provider);

            // Check currency support
            if (!$provider->supportsCurrency($paymentRequest->currencyCode)) {
                return $this->errorResponse("Currency {$paymentRequest->currencyCode} is not supported by {$provider->getProviderName()} provider");
            }

            // Validate payment code if provider supports it
            $codeValidation = $this->validatePaymentCode($provider, $paymentRequest);
            if (!$codeValidation['success']) {
                return $this->errorResponse($codeValidation['message']);
            }

            // Process payment based on type
            $paymentResponse = $this->executePayment($provider, $paymentRequest);

            if ($paymentResponse->success) {
                // Handle successful payment
                $this->handleSuccessfulPayment($paymentRequest, $paymentResponse);
                return $this->successResponse(translate($paymentResponse->message));
            } else {
                return $this->errorResponse(translate($paymentResponse->message));
            }

        } catch (Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'payment_request' => $paymentRequest->toArray(),
                'exception' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $provider, string $requestId): PaymentResponse
    {
        try {
            $paymentProvider = $this->paymentFactory->createProvider($provider);
            return $paymentProvider->checkPaymentStatus($requestId);
        } catch (Exception $e) {
            Log::error("Payment status check failed: {$e->getMessage()}", [
                'provider' => $provider,
                'request_id' => $requestId
            ]);

            return PaymentResponse::failure($e->getMessage());
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(string $provider, string $requestId, string $referenceId, float $amount, string $currencyCode): PaymentResponse
    {
        try {
            $paymentProvider = $this->paymentFactory->createProvider($provider);

            if (!$paymentProvider->supportsCurrency($currencyCode)) {
                return PaymentResponse::failure("Currency {$currencyCode} is not supported by {$provider} provider");
            }

            return $paymentProvider->refundPayment($requestId, $referenceId, $amount, $currencyCode);
        } catch (Exception $e) {
            Log::error("Payment refund failed: {$e->getMessage()}", [
                'provider' => $provider,
                'request_id' => $requestId,
                'reference_id' => $referenceId,
                'amount' => $amount,
                'currency_code' => $currencyCode
            ]);

            return PaymentResponse::failure($e->getMessage());
        }
    }

    /**
     * Get supported providers
     */
    public function getSupportedProviders(): array
    {
        return $this->paymentFactory->getSupportedProviders();
    }

    /**
     * Get provider supported currencies
     */
    public function getProviderSupportedCurrencies(string $provider): array
    {
        try {
            $paymentProvider = $this->paymentFactory->createProvider($provider);
            return $paymentProvider->getSupportedCurrencies();
        } catch (Exception $e) {
            Log::error("Failed to get supported currencies for provider {$provider}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Validate payment code with provider
     */
    private function validatePaymentCode(PaymentProviderInterface $provider, PaymentRequest $paymentRequest): array
    {
        if (empty($paymentRequest->code)) {
            return ['success' => true, 'message' => 'No code to validate'];
        }

        try {
            $response = $provider->validatePaymentCode(
                $paymentRequest->code,
                $paymentRequest->requestId . '_validation',
                $paymentRequest->userPhone,
                $paymentRequest->amount,
                $paymentRequest->currencyCode
            );

            return [
                'success' => $response->success,
                'message' => $response->message
            ];
        } catch (Exception $e) {
            Log::warning("Payment code validation failed: {$e->getMessage()}");
            // If validation fails, we'll let the payment attempt handle it
            return ['success' => true, 'message' => 'Validation skipped'];
        }
    }

    /**
     * Execute payment with provider
     */
    private function executePayment(PaymentProviderInterface $provider, PaymentRequest $paymentRequest): PaymentResponse
    {
        return $provider->makePayment(
            $paymentRequest->userPhone,
            $paymentRequest->requestId,
            $paymentRequest->code,
            $paymentRequest->amount,
            $paymentRequest->currencyCode,
            [
                'notes' => $paymentRequest->purpose,
                'payment_type' => $paymentRequest->paymentType,
                'user_id' => $paymentRequest->userId,
                'payment_type_id' => $paymentRequest->paymentTypeId,
                'metadata' => $paymentRequest->metadata
            ]
        );
    }

    /**
     * Handle successful payment
     */
    private function handleSuccessfulPayment(PaymentRequest $paymentRequest, PaymentResponse $paymentResponse): void
    {
        $paymentDetails = json_encode($paymentResponse->getRawResponse());

        switch ($paymentRequest->paymentType) {
            case PaymentType::CART_PAYMENT:
                if ($paymentRequest->paymentTypeId) {
                    $this->checkout_done($paymentRequest->paymentTypeId, $paymentDetails, $paymentRequest->provider);
                }
                break;

            case PaymentType::ORDER_REPAYMENT:
                if ($paymentRequest->paymentTypeId) {
                    $this->order_re_payment_done($paymentRequest->paymentTypeId, $paymentRequest->provider, $paymentDetails);
                }
                break;

            case PaymentType::WALLET_PAYMENT:
                $this->wallet_payment_done($paymentRequest->userId, $paymentRequest->amount, $paymentRequest->provider, $paymentDetails);
                break;
            case PaymentType::CAR_INSPECTION_PAYMENT:
               if($paymentRequest->paymentTypeId){
                    $this->carInspectionPaymentDone($paymentRequest->paymentTypeId, $paymentRequest->provider,  $paymentResponse, $paymentDetails, $paymentRequest->amount);
               }
                break;
            case PaymentType::CAR_RESERVATION_PAYMENT:
                if($paymentRequest->paymentTypeId){
                    $this->carReservationPaymentDone($paymentRequest->paymentTypeId, $paymentRequest->provider, $paymentResponse, $paymentDetails, $paymentRequest->amount);
                }
                break;
            case PaymentType::AUCTION_INVOICE_PAYMENT:
                if($paymentRequest->paymentTypeId){
                    $this->auctionInvoicePaymentDone($paymentRequest->paymentTypeId, $paymentRequest->provider, $paymentResponse, $paymentDetails);
                }
                break;
            case PaymentType::AUCTION_INSURANCE_DEPOSIT:
                if($paymentRequest->paymentTypeId){
                    $this->auctionInsuranceDepositPaymentDone($paymentRequest->paymentTypeId, $paymentRequest->provider, $paymentResponse, $paymentDetails);
                }
                break;
        }

        Log::info('Payment processed successfully', [
            'provider' => $paymentRequest->provider,
            'payment_type' => $paymentRequest->paymentType,
            'user_id' => $paymentRequest->userId,
            'amount' => $paymentRequest->amount,
            'currency' => $paymentRequest->currencyCode,
            'transaction_id' => $paymentResponse->transactionId,
            'reference_id' => $paymentResponse->referenceId
        ]);
    }

    /**
     * Create success response
     */
    private function successResponse(string $message): array
    {
        return [
            'success' => true,
            'message' => $message
        ];
    }

    /**
     * Create error response
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }

    /**
     * Validate payment request data
     */
    public function validatePaymentData(array $requestData): array
    {
        $errors = [];

        // Required fields
        $requiredFields = ['payment_type', 'provider'];

        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                $errors[] = "Field {$field} is required";
            }
        }

        // Validate payment type
        if (!empty($requestData['payment_type']) && !PaymentType::isValid($requestData['payment_type'])) {
            $errors[] = "Invalid payment type: {$requestData['payment_type']}";
        }

        // Validate specific payment type requirements
        if (!empty($requestData['payment_type'])) {
            switch ($requestData['payment_type']) {
                case PaymentType::CART_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for cart payments';
                    } else {
                         // validated combined order exists
                        $combinedOrder = CombinedOrder::find($requestData['payment_type_id']);
                        if (!$combinedOrder) {
                            $errors[] = 'Combined order not found';
                        }
                    }

                    break;

                case PaymentType::ORDER_REPAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for cart payments';
                    } else {
                         $order = Order::find($requestData['payment_type_id']);
                        if (!$order) {
                            $errors[] = 'Order not found';
                        } elseif ($order->payment_status === 'paid') {
                            $errors[] = 'Order is already paid';
                        }
                    }

                    break;

                case PaymentType::WALLET_PAYMENT:
                    if (empty($requestData['amount']) || !is_numeric($requestData['amount']) || $requestData['amount'] <= 0) {
                        $errors[] = 'Valid amount is required for wallet payments';
                    }
                    break;
                case PaymentType::CAR_INSPECTION_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for car_inspection payments';
                    } else {
                         $inspection = CarInspection::find($requestData['payment_type_id']);
                        if (!$inspection) {
                            $errors[] = 'Car Inspection not found';
                        }
                    }

                    break;
                case PaymentType::CAR_RESERVATION_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for car_reservation payments';
                    } else {
                        $reservation = CarReservation::find($requestData['payment_type_id']);
                        if (!$reservation) {
                            $errors[] = 'Car Reservation not found';
                        }
                    }

                    break;
                case PaymentType::AUCTION_INVOICE_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for auction_invoice payments';
                    } else {
                        $auctionInvoice = \App\Models\AuctionInvoice::find($requestData['payment_type_id']);
                        if (!$auctionInvoice) {
                            $errors[] = 'Auction invoice not found';
                        } elseif ($auctionInvoice->status === 'paid') {
                            $errors[] = 'Auction invoice is already paid';
                        }
                    }

                    break;
                case PaymentType::AUCTION_INSURANCE_DEPOSIT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for auction_insurance_deposit payments';
                    } else {
                        $insuranceDeposit = \App\Models\UserInsuranceDeposit::find($requestData['payment_type_id']);
                        if (!$insuranceDeposit) {
                            $errors[] = 'Insurance deposit not found';
                        } elseif ($insuranceDeposit->status === 'paid') {
                            $errors[] = 'Insurance deposit is already paid';
                        }
                    }

                    break;
            }
        }

        // Validate user exists
        if (!empty($requestData['user_id'])) {
            $user = User::find($requestData['user_id']);
            if (!$user) {
                $errors[] = 'User not found';
            } elseif (empty($user->phone)) {
                $errors[] = 'User must have a phone number';
            }
        }

        return $errors;
    }

    private function checkout_done($combined_order_id, $payment, $payment_method)
    {
        $combined_order = CombinedOrder::find($combined_order_id);

        foreach ($combined_order->orders as $key => $order) {
            $order->payment_type = $payment_method;
            $order->payment_status = 'paid';
            $order->payment_details = $payment;
            $order->save();
            calculateCommissionAffilationClubPoint($order);
            NotificationUtility::sendOrderPlacedNotification($order);
        }
    }
    private function order_re_payment_done($order_id, $payment_method, $payment_details)
    {
        $order = Order::findOrFail($order_id);
        $order->payment_status = 'paid';
        $order->payment_details = $payment_details;
        $order->payment_type = $payment_method;
        $order->save();

        calculateCommissionAffilationClubPoint($order);
        if($order->notified == 0){
            NotificationUtility::sendOrderPlacedNotification($order);
            $order->notified = 1;
            $order->save();
        }

    }

    private function carReservationPaymentDone($reservation_id, $payment_method, $payment_response, $payment_details, $amount)
    {
        $reservation = CarReservation::findOrFail($reservation_id);
        $reservation->payment()->updateOrCreate([],[
            'method'    => $payment_method,
            'status'    => PaymentStatusEnum::PAID,
            'amount'    => $amount,
            'paid_at'   => now(),
            'transaction_id'    => $payment_response->transactionId,
            'reference_id'  => $payment_response->referenceId,
            'details'   => $payment_details
        ]);

       event(new CarReservationPaid($reservation));
    }

    private function carInspectionPaymentDone($inspection_id, $payment_method, $payment_response, $payment_details, $amount)
    {
        $inspection = CarInspection::findOrFail($inspection_id);
        $inspection->payment()->updateOrCreate([],[
            'method'    => $payment_method,
            'status'    => PaymentStatusEnum::PAID,
            'amount'    => $amount,
            'paid_at'   => now(),
            'transaction_id'    => $payment_response->transactionId,
            'reference_id'  => $payment_response->referenceId,
            'details'   => $payment_details
        ]);

        event(new CarInspectionPaid($inspection));

    }

    private function auctionInvoicePaymentDone($invoice_id, $payment_method, $payment_response, $payment_details)
    {
        $invoice = \App\Models\AuctionInvoice::findOrFail($invoice_id);

        $payment = $invoice->payment()->updateOrCreate([], [
            'method' => $payment_method,
            'status' => PaymentStatusEnum::PAID,
            'transaction_id' => $payment_response->transactionId,
            'reference_id' => $payment_response->referenceId,
            'amount' => $invoice->amount,
            'details' => $payment_details,
            'paid_at' => now()
        ]);

        // Update invoice status
        $invoice->status = 'paid';
        $invoice->payment_id = $payment->id;
        $invoice->paid_at = now();
        $invoice->save();

        // Log audit trail
        \App\Models\AuctionAuditLog::create([
            'auction_room_id' => $invoice->auctionItem->auction_room_id,
            'auction_item_id' => $invoice->auction_item_id,
            'user_id' => $invoice->user_id,
            'action' => 'invoice_paid',
            'details' => [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,
                'amount' => $invoice->amount,
                'payment_method' => $payment_method,
                'transaction_id' => $payment_response->transactionId
            ],
            'ip_address' => request()->ip()
        ]);

        // Dispatch event
        event(new \App\Events\AuctionInvoicePaid($invoice));
    }

    private function auctionInsuranceDepositPaymentDone($deposit_id, $payment_method, $payment_response, $payment_details)
    {
        $deposit = \App\Models\UserInsuranceDeposit::findOrFail($deposit_id);

        $payment = $deposit->payment()->updateOrCreate([], [
            'method' => $payment_method,
            'status' => PaymentStatusEnum::PAID,
            'transaction_id' => $payment_response->transactionId,
            'reference_id' => $payment_response->referenceId,
            'amount' => $deposit->amount,
            'details' => $payment_details,
            'paid_at' => now()
        ]);

        // Update deposit status
        $deposit->status = 'paid';
        $deposit->payment_id = $payment->id;
        $deposit->paid_at = now();
        $deposit->save();

        // Log audit trail
        \App\Models\AuctionAuditLog::create([
            'user_id' => $deposit->user_id,
            'action' => 'insurance_deposit_paid',
            'details' => [
                'deposit_id' => $deposit->id,
                'amount' => $deposit->amount,
                'payment_method' => $payment_method,
                'transaction_id' => $payment_response->transactionId
            ],
            'ip_address' => request()->ip()
        ]);

        // Dispatch event
        event(new \App\Events\AuctionInsuranceDepositPaid($deposit));
    }

    private function wallet_payment_done($user_id, $amount, $payment_method, $payment_details)
    {
        $user = User::findOrFail($user_id);
        $user->incrementBalance($amount, $payment_method, $payment_details);
    }
}
