<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Agent;
use App\Models\Frame;
use App\Models\Faction;
use App\Enums\ShipRoles;
use App\Enums\FlightModes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Enums\CrewRotations;
use App\Enums\ShipNavStatus;
use Spatie\LaravelData\Data;
use InvalidArgumentException;
use Spatie\LaravelData\DataCollection;
use App\Interfaces\GeneratableFromResponse;
use Spatie\LaravelData\Attributes\DataCollectionOf;

class ShipData extends Data implements GeneratableFromResponse
{
    public int $agentId;

    public function __construct(
        public string $symbol,
        public ?FactionData $faction = null,
        public ?int $factionId = null,
        public string $role,
        public string $waypointSymbol,
        public string $status,
        public string $flightMode,
        public int $crewCurrent,
        public int $crewCapacity,
        public int $crewRequired,
        public string $crewRotation,
        public int $crewMorale,
        public int $crewWages,
        public int $fuelCurrent,
        public int $fuelCapacity,
        public int $fuelConsumed,
        public int $cooldown,
        public ?FrameData $frame = null,
        public ?int $frameID = null,
        public int $frameCondition,
        public ?ReactorData $reactor = null,
        public ?int $reactorId = null,
        public int $reactorCondition,
        public ?EngineData $engine = null,
        public ?int $engineId = null,
        public int $engineCondition,
        #[DataCollectionOf(ModuleData::class)]
        public ?DataCollection $modules = null,
        #[DataCollectionOf(MountData::class)]
        public ?DataCollection $mounts = null,
        public int $cargoCapacity,
        public int $cargoUnits,
        #[DataCollectionOf(CargoData::class)]
        public ?DataCollection $inventory = null,
    ) {
        match (true) {
            !ShipRoles::isValid($role) => throw new InvalidArgumentException("Invalid role: {$role}"),
            !ShipNavStatus::isValid($status) => throw new InvalidArgumentException("Invalid status: {$status}"),
            !FlightModes::isValid($flightMode) => throw new InvalidArgumentException("Invalid flight mode: {$flightMode}"),
            !CrewRotations::isValid($crewRotation) => throw new InvalidArgumentException("Invalid crew rotation: {$crewRotation}"),
            default => null,
        };

        $this->agentId = Agent::firstWhere('symbol', Str::beforeLast($symbol, '-'))->id;
    }

    public static function fromResponse(array $response): static
    {
        $faction = Faction::findBySymbol($response['registration']['factionSymbol']);
        $factionData = $faction ? null : FactionData::from($faction);

        $frame = Frame::findBySymbol($response['frame']['symbol']);
        $frameData = $frame ? null : FrameData::fromResponse($response['frame']);

        $reactor = Frame::findBySymbol($response['reactor']['symbol']);
        $reactorData = $reactor ? null : ReactorData::fromResponse($response['reactor']);

        $engine = Frame::findBySymbol($response['reactor']['symbol']);
        $engineData = $engine ? null : EngineData::fromResponse($response['engine']);

        return new self(
            symbol: $response['symbol'],
            faction: $factionData,
            factionId: $faction?->id,
            role: $response['registration']['role'],
            waypointSymbol: $response['nav']['waypointSymbol'],
            status: $response['nav']['status'],
            flightMode: $response['nav']['flightMode'],
            crewCurrent: $response['crew']['current'],
            crewCapacity: $response['crew']['capacity'],
            crewRequired: $response['crew']['required'],
            crewRotation: $response['crew']['rotation'],
            crewMorale: $response['crew']['morale'],
            crewWages: $response['crew']['wages'],
            fuelCurrent: $response['fuel']['current'],
            fuelCapacity: $response['fuel']['capacity'],
            fuelConsumed: $response['fuel']['consumed']['amount'],
            cooldown: $response['cooldown']['remainingSeconds'],
            frame: $frameData,
            frameID: $frame?->id,
            frameCondition: $response['frame']['condition'],
            reactor: $reactorData,
            reactorId: $reactor?->id,
            reactorCondition: $response['reactor']['condition'],
            engine: $engineData,
            engineId: $engine?->id,
            engineCondition: $response['engine']['condition'],
            modules: ModuleData::collection(
                Arr::map($response['modules'],
                fn (array $module) => ModuleData::fromResponse($module))
            ),
            mounts: MountData::collection(
                Arr::map($response['mounts'],
                fn (array $mount) => MountData::fromResponse($mount))
            ),
            cargoCapacity: $response['cargo']['capacity'],
            cargoUnits: $response['cargo']['units'],
            inventory: CargoData::collection(
                Arr::map($response['cargo']['inventory'],
                fn (array $cargo) => CargoData::from($cargo))
            ),
        );
    }
}
