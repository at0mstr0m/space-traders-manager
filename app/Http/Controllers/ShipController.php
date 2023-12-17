<?php

namespace App\Http\Controllers;

use App\Models\Ship;
use App\Jobs\UpdateShips;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShipCollection;

class ShipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ShipCollection::make(Ship::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Ship $ship) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ship $ship) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ship $ship) {}

    /**
     * Refetch all ships.
     */
    public function refetch() {
        UpdateShips::dispatchSync();

        return $this->index();
    }
}
