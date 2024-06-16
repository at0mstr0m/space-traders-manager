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
