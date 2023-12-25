<?php

namespace App\Http\Controllers;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Http\Resources\PotentialTradeRouteResource;
use App\Models\PotentialTradeRoute;
use Illuminate\Http\Request;

class PotentialTradeRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PotentialTradeRouteResource::collection(PotentialTradeRoute::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(PotentialTradeRoute $potentialTradeRoute) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PotentialTradeRoute $potentialTradeRoute) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PotentialTradeRoute $potentialTradeRoute) {}

    /**
     * Refetch all potential trade routes.
     */
    public function refetch()
    {
        UpdateOrRemovePotentialTradeRoutesAction::run();

        return $this->index();
    }
}
