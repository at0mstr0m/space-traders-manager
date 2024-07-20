<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateShipAction;
use App\Enums\ShipTypes;
use App\Enums\TaskTypes;
use App\Enums\TradeSymbols;
use App\Helpers\SpaceTraders;
use App\Http\Requests\BuyShipRequest;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\ShipPurchaseSellRequest;
use App\Http\Requests\UpdateFlightModeRequest;
use App\Http\Requests\UpdateShipTaskRequest;
use App\Http\Resources\ShipResource;
use App\Jobs\UpdateShips;
use App\Models\PotentialTradeRoute;
use App\Models\Ship;
use App\Models\Task;
use App\Models\Waypoint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

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
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        return ShipResource::collection(
            Ship::when(
                $request->hasSort(),
                fn (Builder $query) => $query->orderBy(
                    $request->sortBy(),
                    $request->sortDirection()
                )
            )->paginate(
                $request->perPage(),
                page: $request->page()
            )
        );
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
    public function refetch(PaginationRequest $request): AnonymousResourceCollection
    {
        UpdateShips::dispatchSync();

        return $this->index($request);
    }

    /**
     * Buy ship.
     */
    public function buy(BuyShipRequest $request): ShipResource
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

    /**
     * Update ship's Task.
     */
    public function updateTask(Ship $ship, UpdateShipTaskRequest $request): ShipResource
    {
        $taskId = $request->integer('taskId');

        DB::transaction(function () use ($ship, $taskId) {
            $taskType = $ship?->task?->type;
            if ($taskType && in_array(
                $taskType,
                TaskTypes::interactingWithPotentialTradeRoutes()
            )) {
                PotentialTradeRoute::where('ship_id', $ship->id)->update(['ship_id' => null]);
            }
            $ship->task()->associate(Task::find($taskId))->save();
        });

        return $this->show($ship);
    }

    /**
     * Purchase Cargo.
     */
    public function purchase(Ship $ship, ShipPurchaseSellRequest $request): ShipResource
    {
        return new ShipResource(
            $ship->purchaseCargo(
                $request->enum('symbol', TradeSymbols::class),
                $request->integer('quantity'),
            )
        );
    }

    /**
     * Sell Cargo.
     */
    public function sell(Ship $ship, ShipPurchaseSellRequest $request): ShipResource
    {
        return new ShipResource(
            $ship->sellCargo(
                $request->enum('symbol', TradeSymbols::class),
                $request->integer('quantity'),
            )
        );
    }

    /**
     * Refuel Ship.
     */
    public function refuel(Ship $ship): ShipResource
    {
        return new ShipResource($ship->refuel());
    }

    /**
     * Navigate Ship to Waypoint.
     */
    public function navigate(Ship $ship, Waypoint $waypoint): ShipResource
    {
        return new ShipResource($ship->navigateTo($waypoint->symbol));
    }
}
