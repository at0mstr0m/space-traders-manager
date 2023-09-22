<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumToArray;

enum ShipNavStatus: string
{
    use EnumToArray;

    case IN_TRANSIT = 'IN_TRANSIT';
    case IN_ORBIT = 'IN_ORBIT';
    case DOCKED = 'DOCKED';
}
