<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CargoData;
use App\Data\ModuleData;
use App\Data\MountData;
use App\Data\ShipData;
use App\Models\Agent;
use App\Models\Cargo;
use App\Models\Engine;
use App\Models\Frame;
use App\Models\Module;
use App\Models\Mount;
use App\Models\Reactor;
use App\Models\Ship;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateShipAction
{
    use AsAction;

    public function handle(ShipData $shipData, Agent $agent): Ship
    {
        /** @var Ship */
        $ship = $agent->ships()->updateOrCreate(
            ['symbol' => $shipData->symbol],
            [
                'faction_id' => $shipData->factionId,
                'role' => $shipData->role,
                'waypoint_symbol' => $shipData->waypointSymbol,
                'status' => $shipData->status,
                'flight_mode' => $shipData->flightMode,
                'crew_current' => $shipData->crewCurrent,
                'crew_capacity' => $shipData->crewCapacity,
                'crew_required' => $shipData->crewRequired,
                'crew_rotation' => $shipData->crewRotation,
                'crew_morale' => $shipData->crewMorale,
                'crew_wages' => $shipData->crewWages,
                'fuel_current' => $shipData->fuelCurrent,
                'fuel_capacity' => $shipData->fuelCapacity,
                'fuel_consumed' => $shipData->fuelConsumed,
                'cooldown' => $shipData->cooldown,
                'frame_condition' => $shipData->frameCondition,
                'frame_integrity' => $shipData->frameIntegrity,
                'reactor_condition' => $shipData->reactorCondition,
                'reactor_integrity' => $shipData->reactorIntegrity,
                'engine_condition' => $shipData->engineCondition,
                'engine_integrity' => $shipData->engineIntegrity,
                'cargo_capacity' => $shipData->cargoCapacity,
                'cargo_units' => $shipData->cargoUnits,
                'frame_id' => $this->resolveFrame($shipData)->id,
                'reactor_id' => $this->resolveReactor($shipData)->id,
                'engine_id' => $this->resolveEngine($shipData)->id,
            ]
        );

        if ($ship->has_reached_destination) {
            $ship->update(['destination' => null]);
        }

        // update modules
        $ship->modules()->sync(
            $shipData->modules
                ->unique('symbol')
                ->map(
                    fn (ModuleData $moduleData) => Module::updateOrCreate(
                        ['symbol' => $moduleData->symbol],
                        [
                            'name' => $moduleData->name,
                            'description' => $moduleData->description,
                            'capacity' => $moduleData?->capacity,
                            'range' => $moduleData?->range,
                            'required_power' => $moduleData->requiredPower,
                            'required_crew' => $moduleData->requiredCrew,
                            'required_slots' => $moduleData->requiredSlots,
                        ]
                    )->only(['id', 'symbol'])
                )->mapWithKeys(
                    fn (array $module) => [
                        $module['id'] => [
                            'quantity' => $shipData->modules
                                ->where('symbol', $module['symbol']->value)
                                ->count(),
                        ],
                    ]
                )
        );

        // update mounts
        $ship->mounts()->sync(
            $shipData->mounts
                ->unique('symbol')
                ->map(
                    fn (MountData $mountData) => Mount::updateOrCreate(
                        ['symbol' => $mountData->symbol],
                        [
                            'name' => $mountData->name,
                            'description' => $mountData->description,
                            'strength' => $mountData?->strength,
                            'required_power' => $mountData->requiredPower,
                            'required_crew' => $mountData->requiredCrew,
                        ]
                    )->only(['id', 'symbol'])
                )->mapWithKeys(
                    fn (array $mount) => [
                        $mount['id'] => [
                            'quantity' => $shipData->mounts
                                ->where('symbol', $mount['symbol']->value)
                                ->count(),
                        ],
                    ]
                )
        );

        // update inventory
        $ship->cargos()->delete();
        $shipData->inventory->each(
            fn (CargoData $cargoData) => Cargo::new($cargoData)->ship()->associate($ship)->save()
        );

        $ship->save();

        return $ship->refresh();
    }

    private function resolveFrame(ShipData $shipData): Frame
    {
        return $shipData->frameId
            ? Frame::find($shipData->frameId)
            : Frame::updateOrCreate(
                ['symbol' => $shipData->frame->symbol],
                [
                    'name' => $shipData->frame->name,
                    'description' => $shipData->frame->description,
                    'module_slots' => $shipData->frame->moduleSlots,
                    'mounting_points' => $shipData->frame->mountingPoints,
                    'fuel_capacity' => $shipData->frame->fuelCapacity,
                    'required_power' => $shipData->frame->requiredPower,
                    'required_crew' => $shipData->frame->requiredCrew,
                ],
            );
    }

    private function resolveReactor(ShipData $shipData): Reactor
    {
        return $shipData->reactorId
            ? Reactor::find($shipData->reactorId)
            : Reactor::updateOrCreate(
                ['symbol' => $shipData->reactor->symbol],
                [
                    'name' => $shipData->reactor->name,
                    'description' => $shipData->reactor->description,
                    'power_output' => $shipData->reactor->powerOutput,
                    'required_crew' => $shipData->reactor->requiredCrew,
                ],
            );
    }

    private function resolveEngine(ShipData $shipData): Engine
    {
        return $shipData->engineId
            ? Engine::find($shipData->engineId)
            : Engine::updateOrCreate(
                ['symbol' => $shipData->engine->symbol],
                [
                    'name' => $shipData->engine->name,
                    'description' => $shipData->engine->description,
                    'speed' => $shipData->engine->speed,
                    'required_power' => $shipData->engine->requiredPower,
                    'required_crew' => $shipData->engine->requiredCrew,
                ],
            );
    }
}
