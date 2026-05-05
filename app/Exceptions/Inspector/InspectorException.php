<?php

namespace App\Exceptions\Inspector;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class InspectorException extends Exception
{
    protected string $errorCode;
    protected array $details = [];
    protected int $statusCode = 400;

    public function __construct(string $message = '', array $details = [], ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->details = $details;
    }

    /**
     * Get the error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get error details
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $this->getMessage(),
                'code' => $this->getErrorCode(),
                'details' => $this->getDetails(),
            ]
        ], $this->getStatusCode());
    }
}