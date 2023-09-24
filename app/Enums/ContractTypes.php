<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumToArray;

enum ContractTypes: string
{
    use EnumToArray;

    case PROCUREMENT = 'PROCUREMENT';
    case TRANSPORT = 'TRANSPORT';
    case SHUTTLE = 'SHUTTLE';
}
