<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WaypointData;
use App\Data\WaypointModifierData;
use App\Data\WaypointTraitData;
use App\Enums\WaypointModifierSymbols;
use App\Enums\WaypointTraitSymbols;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\Faction;
use App\Models\Model;
use App\Models\Waypoint;
use App\Models\WaypointModifier;
use App\Models\WaypointTrait;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\DataCollection;

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
            /** @var Waypoint */
            $waypoint = Waypoint::updateOrCreate(
                ['symbol' => $waypointData->symbol],
                [
                    'type' => $waypointData->type,
                    'x' => $waypointData->x,
                    'y' => $waypointData->y,
                    'faction_id' => Faction::findBySymbol($waypointData->faction)->id,
                    'orbits' => $waypointData->orbits,
                    'is_under_construction' => $waypointData->isUnderConstruction,
                ]
            );

            $waypoint->traits()->sync(
                $waypointTraits->whereIn(
                    'symbol',
                    $waypointData->traits
                        ->toCollection()
                        ->map(fn (WaypointTraitData $waypointTraitData) => WaypointTraitSymbols::from($waypointTraitData->symbol))
                )->pluck('id')
                    ->all()
            );

            $waypoint->modifiers()->sync(
                $waypointModifiers->whereIn(
                    'symbol',
                    $waypointData->modifiers
                        ->toCollection()
                        ->map(fn (WaypointModifierData $waypointTraitData) => WaypointModifierSymbols::from($waypointTraitData->symbol))
                )->pluck('id')
                    ->all()
            );
        });
    }

    /**
     * @return Collection<Model>
     */
    private function extractRelation(Collection $waypoints, string $key, string $modelClass): Collection
    {
        return $waypoints->pluck($key)
            ->map(fn (DataCollection $dataCollection) => $dataCollection->items())
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
