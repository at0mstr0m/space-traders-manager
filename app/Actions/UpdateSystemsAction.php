<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SystemData;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSystemsAction
{
    use AsAction;

    public function handle(): void
    {
        /** @var SpaceTraders $api */
        $api = app(SpaceTraders::class);

        LocationHelper::systemsWithShips()
            ->map(fn (string $systemSymbol) => $api->getSystem($systemSymbol))
            ->each(fn (SystemData $systemData) => UpdateSystemAction::run($systemData));
    }
}
