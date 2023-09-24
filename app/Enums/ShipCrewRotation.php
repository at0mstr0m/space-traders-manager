<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum CrewRotations: string
{
    use EnumUtils;

    case STRICT = 'STRICT';
    case RELAXED = 'RELAXED';
}
