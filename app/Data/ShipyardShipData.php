<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ShipTypes;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ShipyardShipData extends Data
{
    /**
     * @param Collection<int, ModuleData> $modules
     * @param Collection<int, MountData> $mounts
     */
    public function __construct(
        #[MapInputName('symbol')]
        #[WithCast(EnumCast::class)]
        public ShipTypes $type,
        #[MapInputName('name')]
        public string $name,
        #[MapInputName('description')]
        public string $description,
        #[MapInputName('purchasePrice')]
        public int $purchasePrice,
        #[MapInputName('frame')]
        public FrameData $frame,
        #[MapInputName('reactor')]
        public ReactorData $reactor,
        #[MapInputName('engine')]
        public EngineData $engine,
        #[MapInputName('crew.capacity')]
        public int $crewCapacity,
        #[MapInputName('crew.required')]
        public int $crewRequired,
        #[MapInputName('modules')]
        public Collection $modules,
        #[MapInputName('mounts')]
        public Collection $mounts,
        #[MapInputName('waypointSymbol')]
        public ?string $waypointSymbol = null,
    ) {}

    public function setWaypointSymbol(string $waypointSymbol): self
    {
        $this->waypointSymbol = $waypointSymbol;

        return $this;
    }
}
