<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum ShipConditionEventComponents: string
{
    use EnumUtils;

    case FRAME = 'FRAME';
    case REACTOR = 'REACTOR';
    case ENGINE = 'ENGINE';
}
