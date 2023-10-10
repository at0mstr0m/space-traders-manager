<?php

declare(strict_types=1);

use App\Helpers\SpaceTraders;

if (!function_exists('api')) {
    function api(): SpaceTraders
    {
        return app(SpaceTraders::class);
    }
}
