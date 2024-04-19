<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SystemData;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Models\Faction;
use App\Models\System;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSystemsAction
{
    use AsAction;

    public function handle()
    {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        LocationHelper::systemsWithShips()
            ->map(fn (string $systemSymbol) => $api->getSystem($systemSymbol))
            ->each(
                fn (SystemData $systemData) => System::updateOrCreate(
                    ['symbol' => $systemData->symbol],
                    [
                        'sector_symbol' => $systemData->sectorSymbol,
                        'type' => $systemData->type,
                        'x' => $systemData->x,
                        'y' => $systemData->y,
                    ]
                )->factions()->sync(
                    Arr::map($systemData->factions, fn (string $factionSymbol) => Faction::findBySymbol($factionSymbol)->id)
                )
            );
    }
}
