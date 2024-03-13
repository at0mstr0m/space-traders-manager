<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum TaskTypes: string
{
    use EnumUtils;

    case COLLECTIVE_MINING = 'COLLECTIVE_MINING';
    case COLLECTIVE_SIPHONING = 'COLLECTIVE_SIPHONING';
    CASE SUPPORT_COLLECTIVE_MINERS = 'SUPPORT_COLLECTIVE_MINERS';
    CASE SERVE_TRADE_ROUTE = 'SERVE_TRADE_ROUTE';
}
