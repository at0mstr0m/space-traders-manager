<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum TransactionTypes: string
{
    use EnumUtils;

    case PURCHASE = 'PURCHASE';
    case SELL = 'SELL';
    case REPAIR = 'REPAIR';
    case MODIFICATION = 'MODIFICATION';
}
