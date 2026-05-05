<?php

namespace App\DTOs\Payment;

use App\Enums\PaymentType;

class PaymentRequest
{
    public string $paymentType;
    public int $userId;
    public string $userPhone;
    public string $code;
    public float $amount;
    public string $currencyCode;
    public string $requestId;
    public ?int $paymentTypeId;

    public string $purpose;
    public string $provider;
    public array $metadata;

    public function __construct(
        string $paymentType,
        int $userId,
        string $userPhone,
        string $code,
        float $amount,
        string $currencyCode,
        string $requestId,
        ?int $paymentTypeId = null,
        string $purpose = '',
        string $provider = '',
        array $metadata = []
    ) {
        $this->paymentType = $paymentType;
        $this->userId = $userId;
        $this->userPhone = $userPhone;
        $this->code = $code;
        $this->amount = $amount;
        $this->currencyCode = $currencyCode;
        $this->requestId = $requestId;
        $this->paymentTypeId = $paymentTypeId;
        $this->purpose = $purpose;
        $this->provider = $provider;
        $this->metadata = $metadata;
    }

    /**
     * Create from HTTP request
     */
    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        $systemCurrency = get_system_currency();
        $currencyCode = $systemCurrency->code;

        if ($request->header('Currency-Code') && $request->header('Currency-Code') !== $systemCurrency->code) {
            $currencyCode = $request->header('Currency-Code');
        }

        // Generate request ID based on payment type
        $requestId = self::generateRequestId($request->payment_type);

        return new self(
            paymentType: $request->payment_type,
            userId: $request->user()->id,
            userPhone: $request->user()->phone ?? '',
            code: $request->purchase_code ?? $request->voucher ?? '',
            amount: self::calculateAmount($request),
            currencyCode: $currencyCode,
            requestId: $requestId,
            paymentTypeId: $request->payment_type_id ?? null,
            purpose: $request->purpose ?? self::generatePurpose($request->payment_type, $requestId),
            provider: $request->provider ?? '',
            metadata: $request->metadata ?? []
        );
    }

    /**
     * Check if this is a cart payment
     */
    public function isCartPayment(): bool
    {
        return $this->paymentType === PaymentType::CART_PAYMENT;
    }

    /**
     * Check if this is a wallet payment
     */
    public function isWalletPayment(): bool
    {
        return $this->paymentType === PaymentType::WALLET_PAYMENT;
    }

    /**
     * Check if this is an order re-payment
     */
    public function isOrderRePayment(): bool
    {
        return $this->paymentType === PaymentType::ORDER_REPAYMENT;
    }

    /**
     * Check if car inspection payment
     */
    public function isCarInspectionPayment(): bool
    {
        return $this->paymentType === PaymentType::CAR_INSPECTION_PAYMENT;
    }

    /**
     * Check if car reservation payment
     */

    public function isCarReservationPayment(): bool
    {
        return $this->paymentType === PaymentType::CAR_RESERVATION_PAYMENT;
    }

    /**
     * Check if auction invoice payment
     */
    public function isAuctionInvoicePayment(): bool
    {
        return $this->paymentType === PaymentType::AUCTION_INVOICE_PAYMENT;
    }

    /**
     * Check if auction insurance deposit payment
     */
    public function isAuctionInsuranceDeposit(): bool
    {
        return $this->paymentType === PaymentType::AUCTION_INSURANCE_DEPOSIT;
    }


    /**
     * Generate request ID
     */
    private static function generateRequestId(string $paymentType): string
    {
        $suffix = match ($paymentType) {
            PaymentType::CART_PAYMENT => 'C',
            PaymentType::WALLET_PAYMENT => 'W',
            PaymentType::ORDER_REPAYMENT => 'R',
            PaymentType::CAR_INSPECTION_PAYMENT   => "IN",
            PaymentType::CAR_RESERVATION_PAYMENT   => "RE",
            PaymentType::AUCTION_INVOICE_PAYMENT => 'AIN',
            PaymentType::AUCTION_INSURANCE_DEPOSIT => 'AID',
            default => 'P'
        };

        return date('Ymd-His') . rand(10, 99) . $suffix;
    }

    /**
     * Calculate amount based on payment type
     */
    private static function calculateAmount(\Illuminate\Http\Request $request): float
    {
        switch ($request->payment_type) {
            case PaymentType::CART_PAYMENT:
                if ($request->payment_type_id) {
                    $combinedOrder = \App\Models\CombinedOrder::find($request->payment_type_id);
                    return $combinedOrder ? (float) convert_price($combinedOrder->grand_total) : 0.0;
                }
                return 0.0;

            case PaymentType::ORDER_REPAYMENT:
                if ($request->payment_type_id) {
                    $order = \App\Models\Order::find($request->payment_type_id);
                    return $order ? (float) convert_price($order->grand_total) : 0.0;
                }
                return 0.0;
                case PaymentType::CAR_INSPECTION_PAYMENT:
                    if($request->payment_type_id){
                        $inspection = \App\Models\CarInspection::find($request->payment_type_id);

                        return $inspection?->inspectionType ? (float) convert_price($inspection->inspectionType->price): 0.0;

                    }
                return 0.0;
                case PaymentType::CAR_RESERVATION_PAYMENT:
                    if($request->payment_type_id){
                        $reservation_amount = get_setting('car_reservation_amount');
                        return $reservation_amount ? (float) convert_price($reservation_amount) : 0.0;
                    }
                return 0.0;

            case PaymentType::AUCTION_INVOICE_PAYMENT:
                if ($request->payment_type_id) {
                    $invoice = \App\Models\AuctionInvoice::find($request->payment_type_id);
                    return $invoice ? (float) convert_price($invoice->amount) : 0.0;
                }
                return 0.0;

            case PaymentType::AUCTION_INSURANCE_DEPOSIT:
                $amount = get_setting('insurance_deposit_amount', 500.00);
                    return  (float) convert_price($amount);

            case PaymentType::WALLET_PAYMENT:
                return (float) ($request->amount ?? 0);

            default:
                return (float) ($request->amount ?? 0);
        }
    }

    /**
     * Generate purpose message
     */
    private static function generatePurpose(string $paymentType, string $requestId): string
    {
        return match ($paymentType) {
            PaymentType::CART_PAYMENT => "Payment for order: {$requestId}",
            PaymentType::WALLET_PAYMENT => "Wallet recharge: {$requestId}",
            PaymentType::ORDER_REPAYMENT => "Order re-payment: {$requestId}",
            PaymentType::CAR_INSPECTION_PAYMENT    => "Car Inspection: {$requestId}",
            PaymentType::CAR_RESERVATION_PAYMENT=> "Car reservation {$requestId}",
            PaymentType::AUCTION_INVOICE_PAYMENT => "Auction invoice payment: {$requestId}",
            PaymentType::AUCTION_INSURANCE_DEPOSIT => "Auction insurance deposit: {$requestId}",
            default => "Payment for order code: {$requestId}"
        };
    }

    /**
     * Validate the payment request
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->paymentType)) {
            $errors[] = 'Payment type is required';
        }

        if ($this->userId <= 0) {
            $errors[] = 'Valid user ID is required';
        }

        if (empty($this->userPhone)) {
            $errors[] = 'User phone number is required';
        }

        if ($this->isWalletPayment() && $this->amount <= 0) {
            $errors[] = 'Amount must be greater than zero';
        }

        if (empty($this->currencyCode)) {
            $errors[] = 'Currency code is required';
        }

        if (!$this->isWalletPayment() && !$this->paymentTypeId) {
            $errors[] = 'Payment type ID is required for this payment type';
        }

        return $errors;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'payment_type' => $this->paymentType,
            'user_id' => $this->userId,
            'user_phone' => $this->userPhone,
            'code' => $this->code,
            'amount' => $this->amount,
            'currency_code' => $this->currencyCode,
            'request_id' => $this->requestId,
            'payment_type_id' => $this->paymentTypeId,
            'purpose' => $this->purpose,
            'provider' => $this->provider,
            'metadata' => $this->metadata,
        ];
    }
}
