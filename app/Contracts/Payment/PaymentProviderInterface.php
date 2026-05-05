<?php

namespace App\Contracts\Payment;

interface PaymentProviderInterface
{
    /**
     * Initialize the payment provider with authentication
     *
     * @return void
     * @throws \Exception
     */
    public function authenticate(): void;

    /**
     * Make a payment
     *
     * @param string $userPhone
     * @param string $requestId
     * @param string $code
     * @param float $amount
     * @param string $currencyCode
     * @param array $additionalData
     * @return PaymentResponse
     * @throws \Exception
     */
    public function makePayment(string $userPhone, string $requestId, string $code, float $amount, string $currencyCode, array $additionalData = []): PaymentResponse;

    /**
     * Check payment status
     *
     * @param string $requestId
     * @return PaymentResponse
     * @throws \Exception
     */
    public function checkPaymentStatus(string $requestId): PaymentResponse;

    /**
     * Refund a payment
     *
     * @param string $requestId
     * @param string $referenceId
     * @param float $amount
     * @param string $currencyCode
     * @return PaymentResponse
     * @throws \Exception
     */
    public function refundPayment(string $requestId, string $referenceId, float $amount, string $currencyCode): PaymentResponse;

    /**
     * Validate payment code/voucher
     *
     * @param string $code
     * @param string $requestId
     * @param string $userPhone
     * @param float $amount
     * @param string $currencyCode
     * @return PaymentResponse
     * @throws \Exception
     */
    public function validatePaymentCode(string $code, string $requestId, string $userPhone, float $amount, string $currencyCode): PaymentResponse;

    /**
     * Get provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Check if provider supports the given currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function supportsCurrency(string $currencyCode): bool;

    /**
     * Get supported currencies
     *
     * @return array
     */
    public function getSupportedCurrencies(): array;
}
