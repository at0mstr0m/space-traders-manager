<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\LocationHelper;
use App\Http\Resources\MarketGoodResource;
use App\Http\Resources\ShipResource;
use App\Http\Resources\TradeOpportunityResource;
use App\Http\Resources\WaypointResource;
use App\Models\Waypoint;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WaypointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return WaypointResource::collection(
            Waypoint::withCount('ships')
                ->searchBySymbol()
                ->when(
                    $request->boolean('onlyAsteroids'),
                    fn (Builder $query) => $query->onlyCanBeMined()
                )
                ->when(
                    $request->boolean('onlyGasGiants'),
                    fn (Builder $query) => $query->onlyCanBeSiphoned()
                )
                ->orderBy('symbol')
                ->paginate()
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Waypoint $waypoint): WaypointResource
    {
        return new WaypointResource($waypoint);
    }

    /**
     * Waypoints without Satellites.
     */
    public function withoutSatellite(): AnonymousResourceCollection
    {
        return WaypointResource::collection(LocationHelper::marketplacesWithoutSatellite());
    }

    /**
     * Trade opportunities at this waypoint.
     */
    public function tradeOpportunities(Waypoint $waypoint): AnonymousResourceCollection
    {
        return TradeOpportunityResource::collection(
            $waypoint->tradeOpportunities
        );
    }

    /**
     * Market goods at this waypoint.
     */
    public function marketGoods(Waypoint $waypoint): AnonymousResourceCollection
    {
        return MarketGoodResource::collection(
            $waypoint->marketGoods
        );
    }

    /**
     * Ships at this waypoint.
     */
    public function ships(Waypoint $waypoint): AnonymousResourceCollection
    {
        return ShipResource::collection(
            $waypoint->ships
        );
    }
}
