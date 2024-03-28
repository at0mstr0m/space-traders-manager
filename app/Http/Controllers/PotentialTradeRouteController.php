<?php

namespace App\Http\Controllers;

use App\Actions\UpdateOrRemovePotentialTradeRoutesAction;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\PotentialTradeRouteResource;
use App\Models\PotentialTradeRoute;
use Illuminate\Database\Eloquent\Builder;
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
            )
                // ->where('distance', '<=', 600)
                ->paginate(
                    $request->perPage(),
                    page: $request->page()
                )
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(PotentialTradeRoute $potentialTradeRoute): PotentialTradeRouteResource
    {
        return new PotentialTradeRouteResource($potentialTradeRoute);
    }

    /**
     * Refetch all potential trade routes.
     */
    public function refetch(PaginationRequest $request)
    {
        UpdateOrRemovePotentialTradeRoutesAction::run();

        return $this->index($request);
    }
}
