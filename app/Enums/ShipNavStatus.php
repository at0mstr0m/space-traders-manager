<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum ShipNavStatus: string
{
    use EnumUtils;

    case IN_TRANSIT = 'IN_TRANSIT';
    case IN_ORBIT = 'IN_ORBIT';
    case DOCKED = 'DOCKED';
}
