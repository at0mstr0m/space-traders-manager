<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum FlightModes: string
{
    use EnumUtils;

    case DRIFT = 'DRIFT';
    case STEALTH = 'STEALTH';
    case CRUISE = 'CRUISE';
    case BURN = 'BURN';
}
