<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\CrewRotations;
use App\Enums\FlightModes;
use App\Enums\ShipNavStatus;
use App\Enums\ShipRoles;
use App\Interfaces\GeneratableFromResponse;
use App\Models\Agent;
use App\Models\Engine;
use App\Models\Faction;
use App\Models\Frame;
use App\Models\Reactor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ShipData extends Data implements GeneratableFromResponse
{
    public int $agentId;

    public function __construct(
        public string $symbol,
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
        public int $cargoCapacity,
        public int $cargoUnits,
        public float $frameCondition,
        public float $frameIntegrity,
        public float $reactorCondition,
        public float $reactorIntegrity,
        public float $engineCondition,
        public float $engineIntegrity,
        public ?int $factionId = null,
        public ?FrameData $frame = null,
        public ?int $frameId = null,
        public ?ReactorData $reactor = null,
        public ?int $reactorId = null,
        public ?EngineData $engine = null,
        public ?int $engineId = null,
        #[DataCollectionOf(ModuleData::class)]
        public ?DataCollection $modules = null,
        #[DataCollectionOf(MountData::class)]
        public ?DataCollection $mounts = null,
        #[DataCollectionOf(CargoData::class)]
        public ?DataCollection $inventory = null,
    ) {
        match (true) {
            !ShipRoles::isValid($role) => throw new \InvalidArgumentException("Invalid role: {$role}"),
            !ShipNavStatus::isValid($status) => throw new \InvalidArgumentException("Invalid status: {$status}"),
            !FlightModes::isValid($flightMode) => throw new \InvalidArgumentException("Invalid flight mode: {$flightMode}"),
            !CrewRotations::isValid($crewRotation) => throw new \InvalidArgumentException("Invalid crew rotation: {$crewRotation}"),
            default => null,
        };

        $this->agentId = Agent::firstWhere('symbol', Str::beforeLast($symbol, '-'))->id;
    }

    public static function fromResponse(array $response): static
    {
        $frame = Frame::findBySymbol($response['frame']['symbol']);
        $frameData = $frame ? null : FrameData::fromResponse($response['frame']);

        $reactor = Reactor::findBySymbol($response['reactor']['symbol']);
        $reactorData = $reactor ? null : ReactorData::fromResponse($response['reactor']);

        $engine = Engine::findBySymbol($response['engine']['symbol']);
        $engineData = $engine ? null : EngineData::fromResponse($response['engine']);

        $now = Carbon::now();
        $arrival = Carbon::parse($response['nav']['route']['arrival']);
        $diffInSeconds = $now->isBefore($arrival)
            ? $now->diffInSeconds($arrival)
            : 0;
        $cooldown = max($response['cooldown']['remainingSeconds'], $diffInSeconds);

        return new static(
            symbol: $response['symbol'],
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
            cooldown: $cooldown,
            frameCondition: (float) $response['frame']['condition'],
            frameIntegrity: (float) $response['frame']['integrity'],
            reactorCondition: (float) $response['reactor']['condition'],
            reactorIntegrity: (float) $response['reactor']['integrity'],
            engineCondition: (float) $response['engine']['condition'],
            engineIntegrity: (float) $response['engine']['integrity'],
            factionId: Faction::findBySymbol($response['registration']['factionSymbol'])->id,
            frame: $frameData,
            frameId: $frame?->id,
            reactor: $reactorData,
            reactorId: $reactor?->id,
            engine: $engineData,
            engineId: $engine?->id,
            modules: ModuleData::collectionFromResponse($response['modules']),
            mounts: MountData::collectionFromResponse($response['mounts']),
            cargoCapacity: $response['cargo']['capacity'],
            cargoUnits: $response['cargo']['units'],
            inventory: CargoData::collectionFromResponse($response['cargo']['inventory']),
        );
    }
}
