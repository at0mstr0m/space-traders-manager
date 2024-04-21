<?php

namespace App\Actions;

use App\Data\SystemData;
use App\Models\Faction;
use App\Models\System;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSystemAction
{
    use AsAction;

    public function handle(SystemData $systemData): System
    {
        $system = System::updateOrCreate(
            ['symbol' => $systemData->symbol],
            [
                'sector_symbol' => $systemData->sectorSymbol,
                'type' => $systemData->type,
                'x' => $systemData->x,
                'y' => $systemData->y,
            ]
        );

        $system->factions()->sync(
            Arr::map(
                $systemData->factions,
                fn (string $factionSymbol) => Faction::findBySymbol($factionSymbol)->id
            )
        );

        return $system;
    }
}
