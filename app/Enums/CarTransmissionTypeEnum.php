<?php

namespace App\Enums;

use App\Enums\BaseEnum;

class CarTransmissionTypeEnum extends BaseEnum
{
    public const AUTOMATIC = 'automatic';
    public const MANUAL = 'manual';
    public const SEMI_AUTOMATIC = 'semi_automatic';
    public const CVT = 'cvt';
    public const DUAL_CLUTCH = 'dual_clutch';

}
