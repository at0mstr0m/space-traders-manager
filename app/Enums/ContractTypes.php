<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum ContractTypes: string
{
    use EnumUtils;

    case PROCUREMENT = 'PROCUREMENT';
    case TRANSPORT = 'TRANSPORT';
    case SHUTTLE = 'SHUTTLE';
}
