<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\WaypointData;
use App\Models\Faction;
use App\Models\Waypoint;
use App\Models\WaypointModifier;
use App\Models\WaypointTrait;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateWaypointAction
{
    use AsAction;

    public function handle(WaypointData $waypointData): Waypoint
    {
        /** @var ?int */
        $factionId = $waypointData->faction
            ? Faction::findBySymbol($waypointData->faction)->id
            : null;
        /** @var Waypoint */
        $waypoint = Waypoint::updateOrCreate(
            ['symbol' => $waypointData->symbol],
            [
                'system_symbol' => $waypointData->systemSymbol,
                'type' => $waypointData->type,
                'x' => $waypointData->x,
                'y' => $waypointData->y,
                'faction_id' => $factionId,
                'orbits' => $waypointData->orbits,
                'is_under_construction' => $waypointData->isUnderConstruction,
            ]
        );

        $waypoint->traits()
            ->sync(
                WaypointTrait::select('id')
                    ->whereIn(
                        'symbol',
                        $waypointData->traits->pluck('symbol')
                    )->pluck('id')
            );

        $waypoint->modifiers()->sync(
            WaypointModifier::select('id')
                ->whereIn(
                    'symbol',
                    $waypointData->modifiers->pluck('symbol')
                )->pluck('id')
        );

        return $waypoint;
    }
}
