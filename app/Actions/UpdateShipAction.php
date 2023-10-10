<?php

namespace App\Actions;

use App\Models\Ship;
use App\Models\Agent;
use App\Models\Cargo;
use App\Models\Frame;
use App\Models\Mount;
use App\Data\ShipData;
use App\Models\Engine;
use App\Models\Module;
use App\Data\CargoData;
use App\Data\MountData;
use App\Models\Deposit;
use App\Models\Reactor;
use App\Data\ModuleData;
use App\Data\DepositData;
use Illuminate\Support\Collection;
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
                'symbol' => $shipData->symbol,
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
                'reactor_condition' => $shipData->reactorCondition,
                'engine_condition' => $shipData->engineCondition,
                'cargo_capacity' => $shipData->cargoCapacity,
                'cargo_units' => $shipData->cargoUnits,
                'frame_id' => $this->resolveFrame($shipData)->id,
                'reactor_id' => $this->resolveReactor($shipData)->id,
                'engine_id' => $this->resolveEngine($shipData)->id,
            ]
        );

        // update modules
        $ship->modules()->sync([]);

        $shipData->modules?->reduce(
            fn (Collection $carry, ModuleData $moduleData) => $carry->push(
                Module::updateOrCreate(
                    ['symbol' => $moduleData->symbol],
                    [
                        'symbol' => $moduleData->symbol,
                        'name' => $moduleData->name,
                        'description' => $moduleData->description,
                        'capacity' => $moduleData?->capacity,
                        'range' => $moduleData?->range,
                        'required_power' => $moduleData->requiredPower,
                        'required_crew' => $moduleData->requiredCrew,
                        'required_slots' => $moduleData->requiredSlots,
                    ]
                )->id
            ),
            collect()
        )->countBy()
            ->each(
                fn (int $quantity, int $moduleId) =>
                $ship->modules()->attach($moduleId, ['quantity' => $quantity])
            );

        // update mounts
        $ship->mounts()->sync([]);
        $shipData->mounts?->reduce(
            function (Collection $carry, MountData $mountData) {
                $mount = Mount::updateOrCreate(
                    ['symbol' => $mountData->symbol],
                    [
                        'symbol' => $mountData->symbol,
                        'name' => $mountData->name,
                        'description' => $mountData->description,
                        'strength' => $mountData?->strength,
                        'required_power' => $mountData->requiredPower,
                        'required_crew' => $mountData->requiredCrew,
                    ]
                );
                $depositsData = $mountData->deposits;
                if ($depositsData->count()) {
                    $mount->deposits()->sync([]);
                    $depositsData?->reduce(
                        fn (Collection $carry, DepositData $depositData) => $carry->push(
                            Deposit::firstOrCreate(['symbol' => $depositData->symbol])->id
                        ),
                        collect()
                    )->pipe(fn (Collection $depositIds) => $mount->deposits()->sync($depositIds));
                }

                return $carry->push($mount->id);
            },
            collect()
        )->countBy()
            ->each(
                fn (int $quantity, int $mountId) =>
                $ship->mounts()->attach($mountId, ['quantity' => $quantity])
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
                    'symbol' => $shipData->frame->symbol,
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
                    'symbol' => $shipData->reactor->symbol,
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
                    'symbol' => $shipData->engine->symbol,
                    'name' => $shipData->engine->name,
                    'description' => $shipData->engine->description,
                    'speed' => $shipData->engine->speed,
                    'required_power' => $shipData->engine->requiredPower,
                    'required_crew' => $shipData->engine->requiredCrew,
                ],
            );
    }
}
