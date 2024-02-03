<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum WaypointModifierSymbols: string
{
    use EnumUtils;

    case STRIPPED = 'STRIPPED';
    case UNSTABLE = 'UNSTABLE';
    case RADIATION_LEAK = 'RADIATION_LEAK';
    case CRITICAL_LIMIT = 'CRITICAL_LIMIT';
    case CIVIL_UNREST = 'CIVIL_UNREST';
}
