<?php

namespace App\Services\Payment\Providers;

use App\Services\Payment\Providers\AbstractPaymentProvider;
use App\Contracts\Payment\PaymentResponse;
use App\Models\User;
use Exception;

class WalletProvider extends AbstractPaymentProvider
{


    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig([]);
    }

    public function getProviderName(): string
    {
        return "wallet";
    }

     public function getSupportedCurrencies(): array
    {
        return ['YER', 'USD', 'SAR'];
    }

    public function authenticate(): void
    {
        //
    }

    public function makePayment(
        string $userPhone,
        string $requestId,
        string $code,
        float $amount,
        string $currencyCode,
        array $additionalData = [],
    ): PaymentResponse {
        try {

            $user = User::findOrFail($additionalData['user_id']);
            $payment_details = [
                "payment_type" => $additionalData['payment_type'] ?? 'unknown',
                "payment_type_id" => $additionalData['payment_type_id'] ?? null,
            ];
            $converted_amount = convert_price_to_default_currency($amount, $currencyCode);

            if ($user->balance < $converted_amount) {
                return $this->createErrorResponse(
                    "Insufficient wallet balance"
                );
            }
            // Deduct amount from wallet
            $user->decrementBalance($amount, $payment_details);

            return $this->createSuccessResponse(
                "Payment successful using Wallet",
                $additionalData['payment_type_id'] ?? null,
                $requestId
            );
        } catch (Exception $e) {
            return $this->createErrorResponse($e->getMessage());
        }
    }

    public function checkPaymentStatus(string $requestId): PaymentResponse
    {
      return $this->createSuccessResponse(
            "Status check successful",
        );
    }

    public function refundPayment(
        string $requestId,
        string $referenceId,
        float $amount,
        string $currencyCode,
    ): PaymentResponse {
        //TODO: Implement refund logic
        return $this->createErrorResponse(
            "Refunds are not supported by the Wallet provider",
        );
    }

    public function validatePaymentCode(
        string $code,
        string $requestId,
        string $userPhone,
        float $amount,
        string $currencyCode,
    ): PaymentResponse {
        // Wallet doesn't have a separate validation endpoint, so we'll return a generic success
        // The actual validation happens during payment
        return $this->createSuccessResponse(
            "Code validation not available for Jaib provider",
        );
    }
}
