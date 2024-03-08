<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\WaypointResource;
use App\Models\Waypoint;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WaypointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return WaypointResource::collection(Waypoint::paginate());
    }

    /**
     * Display the specified resource.
     */
    public function show(Waypoint $waypoint): WaypointResource
    {
        return new WaypointResource($waypoint);
    }
}
