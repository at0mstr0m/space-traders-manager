<?php

namespace App\Http\Controllers;

use App\Actions\UpdateShipAction;
use App\Enums\ShipTypes;
use App\Helpers\SpaceTraders;
use App\Http\Requests\PurchaseShipRequest;
use App\Http\Resources\ShipResource;
use App\Jobs\UpdateShips;
use App\Models\Ship;

class ShipController extends Controller
{
    private SpaceTraders $api;

    public function __construct()
    {
        $this->api = app(SpaceTraders::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ShipResource::collection(Ship::paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show(Ship $ship)
    {
        return new ShipResource($ship);
    }

    /**
     * Refetch all ships.
     */
    public function refetch()
    {
        UpdateShips::dispatchSync();

        return $this->index();
    }

    /**
     * Purchase ship.
     */
    public function purchase(PurchaseShipRequest $request)
    {
        $validated = $request->validated();
        $purchaseShipData = $this->api->purchaseShip(
            ShipTypes::fromName($validated['shipType']),
            $validated['waypointSymbol']
        );

        return new ShipResource(
            UpdateShipAction::run(
                $purchaseShipData->ship,
                $request->user()->agent
            )
        );
    }
}
