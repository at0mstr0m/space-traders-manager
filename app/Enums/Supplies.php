<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum Supplies: string
{
    use EnumUtils;

    case SCARCE = 'SCARCE';
    case LIMITED = 'LIMITED';
    case MODERATE = 'MODERATE';
    case HIGH = 'HIGH';
    case ABUNDANT = 'ABUNDANT';
}
