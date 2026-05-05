<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class CheckoutException extends Exception
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

}
