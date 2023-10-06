<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum RefinementGood: string
{
    use EnumUtils;

    case IRON = 'IRON';
    case COPPER = 'COPPER';
    case SILVER = 'SILVER';
    case GOLD = 'GOLD';
    case ALUMINUM = 'ALUMINUM';
    case PLATINUM = 'PLATINUM';
    case URANITE = 'URANITE';
    case MERITIUM = 'MERITIUM';
    case FUEL = 'FUEL';
}
