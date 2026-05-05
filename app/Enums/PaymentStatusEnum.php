<?php

namespace App\Enums;

class PaymentStatusEnum extends BaseEnum
{
    public const PENDING    = 'pending';
    public const PAID       = 'paid';
    public const UNPAID     = 'unpaid';
    public const REFUNDED   = 'refunded';
    public const CANCELLED = 'cancelled';
}
