<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\TradeSymbols;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ImportExportExchangeGoodData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public TradeSymbols $symbol,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('waypointSymbol')]
        public ?string $waypointSymbol = null,
    ) {}

    public function setWaypointSymbol(string $waypointSymbol): static
    {
        $this->waypointSymbol = $waypointSymbol;

        return $this;
    }
}
