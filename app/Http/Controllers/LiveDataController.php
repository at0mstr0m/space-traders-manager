<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\WaypointTraitSymbols;
use App\Helpers\LocationHelper;
use App\Helpers\SpaceTraders;
use App\Http\Requests\PaginationRequest;
use App\Models\Waypoint;
use App\Support\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveDataController extends Controller
{
    private SpaceTraders $api;

    public function __construct()
    {
        $this->api = app(SpaceTraders::class);
    }

    public function purchasableShipsInSystem(PaginationRequest $request): LengthAwarePaginator
    {
        $data = Waypoint::whereRelation('traits', 'symbol', WaypointTraitSymbols::SHIPYARD)
            ->onlyHavingShipPresent()
            ->pluck('symbol')
            ->map(fn (string $systemSymbol) => $this->api->getShipyard($systemSymbol))
            ->pluck('ships')
            ->flatten(1)
            ->filter();

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

    public function constructionSiteInStartingSystem(Request $request): JsonResource
    {
        $systemSymbol = LocationHelper::parseSystemSymbol($request->user()->agent->headquarters);

        return new JsonResource(
            LocationHelper::getWaypointUnderConstructionInSystem($systemSymbol)
        );
    }
}
