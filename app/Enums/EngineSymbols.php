<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumToArray;

enum EngineSymbols: string
{
    use EnumToArray;

    case ENGINE_IMPULSE_DRIVE_I = 'ENGINE_IMPULSE_DRIVE_I';
    case ENGINE_ION_DRIVE_I = 'ENGINE_ION_DRIVE_I';
    case ENGINE_ION_DRIVE_II = 'ENGINE_ION_DRIVE_II';
    case ENGINE_HYPER_DRIVE_I = 'ENGINE_HYPER_DRIVE_I';
}
