<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WaypointData;
use App\Data\WaypointModifierData;
use App\Data\WaypointTraitData;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\Faction;
use App\Models\Model;
use App\Models\Waypoint;
use App\Models\WaypointModifier;
use App\Models\WaypointTrait;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateWaypointsAction
{
    use AsAction;

    public function handle()
    {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        $waypoints = LocationHelper::systemsWithShips()
            ->map(fn (string $systemSymbol) => $api->listWaypointsInSystem($systemSymbol, all: true))
            ->flatten(1);

        $waypointTraits = $this->extractRelation($waypoints, 'traits', WaypointTrait::class);
        $waypointModifiers = $this->extractRelation($waypoints, 'modifiers', WaypointModifier::class);

        $waypoints->each(function (WaypointData $waypointData) use ($waypointTraits, $waypointModifiers) {
            /** @var ?int */
            $factionId = $waypointData->faction
                ? Faction::findBySymbol($waypointData->faction)->id
                : null;
            /** @var Waypoint */
            $waypoint = Waypoint::updateOrCreate(
                ['symbol' => $waypointData->symbol],
                [
                    'type' => $waypointData->type,
                    'x' => $waypointData->x,
                    'y' => $waypointData->y,
                    'faction_id' => $factionId,
                    'orbits' => $waypointData->orbits,
                    'is_under_construction' => $waypointData->isUnderConstruction,
                ]
            );

            $waypoint->traits()->sync(
                $waypointTraits->whereIn(
                    'symbol',
                    $waypointData->traits->map(fn (WaypointTraitData $waypointTraitData) => $waypointTraitData->symbol)
                )->pluck('id')
                    ->all()
            );

            $waypoint->modifiers()->sync(
                $waypointModifiers->whereIn(
                    'symbol',
                    $waypointData->modifiers->map(fn (WaypointModifierData $waypointTraitData) => $waypointTraitData->symbol)
                )->pluck('id')
                    ->all()
            );
        });
    }

    /**
     * @return Collection<int, Model>
     */
    private function extractRelation(Collection $waypoints, string $key, string $modelClass): Collection
    {
        return $waypoints->pluck($key)
            ->flatten(1)
            ->unique('symbol')
            ->map(
                fn ($data) => $modelClass::updateOrCreate(
                    ['symbol' => $data->symbol],
                    [
                        'name' => $data->name,
                        'description' => $data->description,
                    ]
                )
            );
    }
}
