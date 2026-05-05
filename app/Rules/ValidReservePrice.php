<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidReservePrice implements ValidationRule
{
    protected $startingBid;
    protected $carValue;

    public function __construct($startingBid = null, $carValue = null)
    {
        $this->startingBid = $startingBid;
        $this->carValue = $carValue;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value) || $value < 0) {
            $fail('The reserve price must be a positive number.');
            return;
        }

        $reservePrice = (float) $value;

        // Check if reserve price is reasonable (not too low)
        if ($reservePrice < 100) {
            $fail('The reserve price must be at least 100.');
            return;
        }

        // Check if reserve price is not less than starting bid
        if ($this->startingBid && $reservePrice < $this->startingBid) {
            $fail('The reserve price cannot be less than the starting bid.');
            return;
        }

        // Check if reserve price is reasonable compared to car value (if available)
        if ($this->carValue && $reservePrice > ($this->carValue * 1.5)) {
            $fail('The reserve price seems too high compared to the estimated car value.');
            return;
        }

        // Check maximum reasonable reserve price (business rule)
        $maxReservePrice = 10000000; // 10 million
        if ($reservePrice > $maxReservePrice) {
            $fail('The reserve price cannot exceed ' . number_format($maxReservePrice) . '.');
        }
    }
}