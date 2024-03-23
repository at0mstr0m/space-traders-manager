<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class PurchaseShipData extends Data
{
    public function __construct(
        #[MapInputName('agent')]
        public AgentData $agent,
        #[MapInputName('ship')]
        public ShipData $ship,
        #[MapInputName('transaction')]
        public TransactionData $transaction,
    ) {}
}
