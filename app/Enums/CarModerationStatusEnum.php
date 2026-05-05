<?php

namespace App\Enums;

class CarModerationStatusEnum extends BaseEnum
{
    public const PENDING    = 'pending';
    public const PUBLISHED  = 'published';
    public const REJECTED   = 'rejected';
}