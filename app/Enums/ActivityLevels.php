<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum ActivityLevels: string
{
    use EnumUtils;

    case WEAK = 'WEAK';
    case GROWING = 'GROWING';
    case STRONG = 'STRONG';
    case RESTRICTED = 'RESTRICTED';
}
