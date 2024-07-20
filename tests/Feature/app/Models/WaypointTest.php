<?php

declare(strict_types=1);

namespace Tests\Feature\App\Models;

use App\Models\MarketGood;
use App\Models\Waypoint;

test('can refuel if is marketplace that sells fuel', function (
    Waypoint $waypoint,
    bool $result
) {
    expect($waypoint->can_refuel)->toBe($result);
})->with([
    fn () => [Waypoint::factory()->isMarketplace()->createOne(), false],
    function () {
        $waypoint = Waypoint::factory()->createOne();
        MarketGood::factory()->atWaypoint($waypoint)->createOne();

        return [$waypoint, false];
    },
    fn () => [Waypoint::factory()->canRefuel()->createOne(), true],
]);
