<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\System;
use App\Enums\WaypointTypes;
use App\Actions\UpdateMarketsAction;
use App\Actions\UpdateWaypointsAction;
use App\Http\Resources\SystemResource;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\WaypointResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SystemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        return SystemResource::collection(
            System::searchBySymbol()
                ->withCount('ships')
                ->withCount('waypoints')
                ->paginate(perPage: $request->perPage())
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(System $system): SystemResource
    {
        return new SystemResource($system);
    }

    /**
     * Display the specified resource.
     */
    public function waypoints(PaginationRequest $request, System $system): AnonymousResourceCollection
    {
        // jumpGate could already be loaded, which is not enough
        if ($system->waypoints()->count() <= 1) {
            UpdateWaypointsAction::run($system->symbol);
        }

        // todo:  move logic from WaypointResource here

        // if ($system->waypoints()->where('type', WaypointTypes::JUMP_GATE)->exists()) {

        // }

        return WaypointResource::collection(
            $system->refresh()
                ->waypoints()
                ->withCount('ships')
                ->orderBy('symbol')
                ->paginate(perPage: $request->perPage())
        );
    }

    public function refetchWaypoints(PaginationRequest $request, System $system): AnonymousResourceCollection
    {
        UpdateWaypointsAction::run($system->symbol);

        UpdateMarketsAction::run($system->symbol);

        return $this->waypoints($request, $system);
    }
}
