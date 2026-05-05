<?php

namespace App\Enums;

class CarStatusEnum extends BaseEnum
{
    public const AVAILABLE    = 'available';
    public const RESERVED     = 'reserved';
    public const IN_AUCTION   = 'in_auction';
    public const SOLD         = 'sold';
}