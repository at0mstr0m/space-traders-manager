<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Http\Requests\PaginationRequest;
use App\Support\LengthAwarePaginator;

class LiveDataController extends Controller
{
    private SpaceTraders $api;

    public function __construct()
    {
        $this->api = app(SpaceTraders::class);
    }

    public function purchasableShipsInSystem(PaginationRequest $request)
    {
        $data = LocationHelper::systemsWithShips()
            ->map(fn (string $systemSymbol) => $this->api->listPurchasableShipsInSystem($systemSymbol))
            ->flatten();

        return new LengthAwarePaginator(
            $data,
            $data->count(),
            $request->perPage(),
            $request->page(),
            [
                'path' => $request->url(),
            ]
        );
    }
}
