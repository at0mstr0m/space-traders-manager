<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateShipAction;
use App\Enums\ShipTypes;
use App\Helpers\SpaceTraders;
use App\Http\Requests\PurchaseShipRequest;
use App\Http\Requests\UpdateFlightModeRequest;
use App\Http\Resources\ShipResource;
use App\Jobs\UpdateShips;
use App\Models\Ship;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
    public function index(): AnonymousResourceCollection
    {
        return ShipResource::collection(Ship::paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show(Ship $ship): ShipResource
    {
        return new ShipResource($ship);
    }

    /**
     * Refetch all ships.
     */
    public function refetch(): AnonymousResourceCollection
    {
        UpdateShips::dispatchSync();

        return $this->index();
    }

    /**
     * Purchase ship.
     */
    public function purchase(PurchaseShipRequest $request): ShipResource
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

    /**
     * Update ship's Flight Mode.
     */
    public function updateFlightMode(Ship $ship, UpdateFlightModeRequest $request): ShipResource
    {
        $flightMode = $request->validated('flightMode');

        return $this->show($ship->setFlightMode($flightMode));
    }
}
