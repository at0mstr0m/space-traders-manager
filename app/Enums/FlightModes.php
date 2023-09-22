<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumToArray;

enum FlightModes: string
{
    use EnumToArray;

    case DRIFT = 'DRIFT';
    case STEALTH = 'STEALTH';
    case CRUISE = 'CRUISE';
    case BURN = 'BURN';
}
