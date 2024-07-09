<?php

declare(strict_types=1);

namespace Tests\Unit\App\Helpers;

use App\Helpers\LocationHelper;

test('parseLocation splits a string at dash', function (string $input, array $output) {
    expect(LocationHelper::parseLocation($input))
        ->toBeArray()
        ->toBe($output);
})->with([
    ['A-1-2', ['A', '1', '2']],
    ['B-2-3', ['B', '2', '3']],
    ['C-3-4', ['C', '3', '4']],
    ['ABC-123', ['ABC', '123']],
]);

it('recognizes System symbols', function (string $input, bool $result) {
    expect(LocationHelper::isSystemSymbol($input))->toBe($result);
})->with([
    ['X1-A0', true],
    ['Y2-B1', true],
    ['Z3-C2', true],
    ['X1-A0-A1', false],
    ['Y2-B1-B2', false],
    ['Z3-C2-C3', false],
    ['X1', false],
    ['X2', false],
]);

it('recognizes Waypoint symbols', function (string $input, bool $result) {
    expect(LocationHelper::isWaypointSymbol($input))->toBe($result);
})->with([
    ['X1-A0-A1', true],
    ['Y2-B1-B2', true],
    ['Z3-C2-C3', true],
    ['X1-A0', false],
    ['Y2-B1', false],
    ['Z3-C2', false],
    ['X1', false],
    ['X2', false],
]);
