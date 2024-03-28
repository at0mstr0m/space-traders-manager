<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use App\Enums\TradeSymbols;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class TransactionData extends Data
{
    public function __construct(
        #[MapInputName('shipSymbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $shipSymbol,
        #[MapInputName('waypointSymbol')]
        public string $waypointSymbol,
        #[MapInputName('agentSymbol')]
        public string $agentSymbol,
        #[MapInputName('price')]
        public int $price,
        #[MapInputName('timestamp')]
        #[WithCast(CarbonCast::class)]
        public Carbon $timestamp,
    ) {
        Transaction::firstOrCreate([
            'agent_symbol' => $this->agentSymbol,
            // Ships are not bought by other ships, so the Command Ship is always the buyer
            'ship_symbol' => $this->agentSymbol . '-1',
            'waypoint_symbol' => $this->waypointSymbol,
            'trade_symbol' => $this->shipSymbol,
            'total_price' => $this->price,
            'timestamp' => $this->timestamp,
            'price_per_unit' => $this->price,
        ]);
    }
}
