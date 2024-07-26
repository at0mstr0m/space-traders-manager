<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ShipConditionEventComponents;
use App\Enums\ShipConditionEvents;
use App\Models\Ship;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ShipConditionEventData extends Data
{
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public ShipConditionEvents $symbol,
        #[MapInputName('component')]
        #[WithCast(EnumCast::class)]
        public ShipConditionEventComponents $component,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        public ?string $shipSymbol = null,
    ) {}

    public function setShipSymbol(string $shipSymbol): static
    {
        $this->shipSymbol = $shipSymbol;

        return $this;
    }

    public function save(?Ship $ship = null): void
    {
        ($ship ?? Ship::findBySymbol($this->shipSymbol))
            ->conditionEvents()
            ->create([
                'symbol' => $this->symbol,
                'component' => $this->component,
                'name' => $this->name,
                'description' => $this->description,
            ]);
    }
}
