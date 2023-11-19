<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum SurveySizes: string
{
    use EnumUtils;

    case SMALL = 'SMALL';
    case MODERATE = 'MODERATE';
    case LARGE = 'LARGE';
}
