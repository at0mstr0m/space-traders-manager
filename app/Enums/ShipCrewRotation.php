<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumToArray;

enum CrewRotations: string
{
    use EnumToArray;

    case STRICT = 'STRICT';
    case RELAXED = 'RELAXED';
}
