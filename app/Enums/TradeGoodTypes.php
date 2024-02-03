<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum TradeGoodTypes: string
{
    use EnumUtils;

    case EXPORT = 'EXPORT';
    case IMPORT = 'IMPORT';
    case EXCHANGE = 'EXCHANGE';
}
