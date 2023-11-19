<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum WaypointTypes: string
{
    use EnumUtils;

    case PLANET = 'PLANET';
    case GAS_GIANT = 'GAS_GIANT';
    case MOON = 'MOON';
    case ORBITAL_STATION = 'ORBITAL_STATION';
    case JUMP_GATE = 'JUMP_GATE';
    case ASTEROID_FIELD = 'ASTEROID_FIELD';
    case ASTEROID = 'ASTEROID';
    case ENGINEERED_ASTEROID = 'ENGINEERED_ASTEROID';
    case ASTEROID_BASE = 'ASTEROID_BASE';
    case NEBULA = 'NEBULA';
    case DEBRIS_FIELD = 'DEBRIS_FIELD';
    case GRAVITY_WELL = 'GRAVITY_WELL';
    case ARTIFICIAL_GRAVITY_WELL = 'ARTIFICIAL_GRAVITY_WELL';
    case FUEL_STATION = 'FUEL_STATION';
}
