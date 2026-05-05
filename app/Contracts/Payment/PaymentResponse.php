<?php

namespace App\Contracts\Payment;

class PaymentResponse
{
    public bool $success;
    public string $message;
    public ?string $transactionId;
    public ?string $referenceId;
    public ?float $amount;
    public ?string $currency;
    public ?string $status;
    public array $rawResponse;
    public array $metadata;

    public function __construct(
        bool $success,
        string $message,
        ?string $transactionId = null,
        ?string $referenceId = null,
        ?float $amount = null,
        ?string $currency = null,
        ?string $status = null,
        array $rawResponse = [],
        array $metadata = []
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->transactionId = $transactionId;
        $this->referenceId = $referenceId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = $status;
        $this->rawResponse = $rawResponse;
        $this->metadata = $metadata;
    }

    /**
     * Create a successful response
     */
    public static function success(
        string $message,
        ?string $transactionId = null,
        ?string $referenceId = null,
        ?float $amount = null,
        ?string $currency = null,
        ?string $status = null,
        array $rawResponse = [],
        array $metadata = []
    ): self {
        return new self(
            true,
            $message,
            $transactionId,
            $referenceId,
            $amount,
            $currency,
            $status,
            $rawResponse,
            $metadata
        );
    }

    /**
     * Create a failed response
     */
    public static function failure(
        string $message,
        array $rawResponse = [],
        array $metadata = []
    ): self {
        return new self(
            false,
            $message,
            null,
            null,
            null,
            null,
            null,
            $rawResponse,
            $metadata
        );
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->success && in_array(strtolower($this->status ?? ''), ['pending', 'processing']);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this->success && in_array(strtolower($this->status ?? ''), ['completed', 'accepted', 'success']);
    }

    /**
     * Check if payment is failed
     */
    public function isFailed(): bool
    {
        return !$this->success || in_array(strtolower($this->status ?? ''), ['failed', 'rejected', 'cancelled', 'canceled']);
    }

    /**
     * Get payment details as array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'transaction_id' => $this->transactionId,
            'reference_id' => $this->referenceId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get raw response for logging/debugging
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }
}
