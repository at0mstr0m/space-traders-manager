<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use App\Enums\FactionSymbols;
use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Models\Engine;
use App\Models\Faction;
use App\Models\Frame;
use App\Models\Reactor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class ShipData extends Data
{
    #[Computed]
    public ?int $factionId;

    #[Computed]
    public ?int $frameId;

    #[Computed]
    public ?int $reactorId;

    #[Computed]
    public ?int $engineId;

    /**
     * @param Collection<int, ModuleData> $modules
     * @param Collection<int, MountData> $mounts
     * @param Collection<int, CargoData> $inventory
     */
    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('registration.role')]
        #[WithCast(EnumCast::class)]
        public ShipRoles $role,
        #[MapInputName('nav.waypointSymbol')]
        public string $waypointSymbol,
        #[MapInputName('nav.status')]
        #[WithCast(EnumCast::class)]
        public ShipNavStatus $status,
        #[MapInputName('nav.flightMode')]
        #[WithCast(EnumCast::class)]
        public FlightModes $flightMode,
        #[MapInputName('nav.route.arrival')]
        #[WithCast(CarbonCast::class)]
        public Carbon $arrival,
        #[MapInputName('crew.current')]
        public int $crewCurrent,
        #[MapInputName('crew.capacity')]
        public int $crewCapacity,
        #[MapInputName('crew.required')]
        public int $crewRequired,
        #[MapInputName('crew.rotation')]
        public string $crewRotation,
        #[MapInputName('crew.morale')]
        public int $crewMorale,
        #[MapInputName('crew.wages')]
        public int $crewWages,
        #[MapInputName('fuel.current')]
        public int $fuelCurrent,
        #[MapInputName('fuel.capacity')]
        public int $fuelCapacity,
        #[MapInputName('fuel.consumed.amount')]
        public int $fuelConsumed,
        #[MapInputName('cooldown.remainingSeconds')]
        public int $cooldown,
        #[MapInputName('cargo.capacity')]
        public int $cargoCapacity,
        #[MapInputName('cargo.units')]
        public int $cargoUnits,
        #[MapInputName('frame.condition')]
        public float $frameCondition,
        #[MapInputName('frame.integrity')]
        public float $frameIntegrity,
        #[MapInputName('reactor.condition')]
        public float $reactorCondition,
        #[MapInputName('reactor.integrity')]
        public float $reactorIntegrity,
        #[MapInputName('engine.condition')]
        public float $engineCondition,
        #[MapInputName('engine.integrity')]
        public float $engineIntegrity,
        #[MapInputName('registration.factionSymbol')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $factionSymbol,
        #[MapInputName('frame')]
        public FrameData $frame,
        #[MapInputName('reactor')]
        public ReactorData $reactor,
        #[MapInputName('engine')]
        public EngineData $engine,
        #[MapInputName('modules')]
        public Collection $modules,
        #[MapInputName('mounts')]
        public Collection $mounts,
        #[MapInputName('cargo.inventory')]
        public Collection $inventory,
    ) {
        $this->factionId = Faction::findBySymbol($factionSymbol->value)?->id;
        $this->frameId = Frame::findBySymbol($frame->symbol->value)?->id;
        $this->reactorId = Reactor::findBySymbol($frame->symbol->value)?->id;
        $this->engineId = Engine::findBySymbol($frame->symbol->value)?->id;

        $now = Carbon::now();
        $this->cooldown = (int) ceil(max(
            $cooldown,
            $now->isBefore($arrival)
                ? $now->diffInSeconds($arrival)
                : 0
        ));
    }
}
