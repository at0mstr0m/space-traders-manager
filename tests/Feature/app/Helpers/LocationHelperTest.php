<?php

declare(strict_types=1);

namespace Tests\Unit\App\Helpers;

use App\Helpers\LocationHelper;
use App\Models\Waypoint;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test(
    'distance is 1 if waypoints have same coordinates',
    function (int|string|Waypoint $waypoint1, int|string|Waypoint $waypoint2) {
        expect(LocationHelper::distance($waypoint1, $waypoint2))
            ->toBe(1);
    }
)->with([
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
        Waypoint::factory()->create(['x' => 1, 'y' => 1]),
    ],
    function () {
        $waypoint = Waypoint::factory()->create(['x' => 1, 'y' => 1]);

        return [
            $waypoint,
            $waypoint,
        ];
    },
    fn () => [
        Waypoint::factory()->create(['x' => 1, 'y' => 1])->symbol,
        Waypoint::factory()->create(['x' => 1, 'y' => 1])->symbol,
    ],
]);
