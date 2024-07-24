<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateWaypointsAction;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\SystemResource;
use App\Http\Resources\WaypointResource;
use App\Models\System;
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
    public function waypoints(System $system): AnonymousResourceCollection
    {
        // jumpGate could already be loaded, which is not enough
        if ($system->waypoints()->count() <= 1) {
            UpdateWaypointsAction::run($system->symbol);
        }

        return WaypointResource::collection(
            $system->refresh()
                ->waypoints()
                ->withCount('ships')
                ->orderBy('symbol')
                ->paginate()
        );
    }
}
