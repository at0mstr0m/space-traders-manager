<?php

namespace App\Http\Controllers;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\PotentialTradeRouteResource;
use App\Models\PotentialTradeRoute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PotentialTradeRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        return PotentialTradeRouteResource::collection(
            PotentialTradeRoute::when(
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
    public function refetch(PaginationRequest $request)
    {
        UpdateOrRemovePotentialTradeRoutesAction::run();

        return $this->index($request);
    }
}
