<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateOrRemoveTradeOpportunitiesAction;
use App\Http\Requests\PaginationRequest;
use App\Http\Resources\TradeOpportunityResource;
use App\Models\TradeOpportunity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeOpportunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PaginationRequest $request): JsonResource
    {
        return TradeOpportunityResource::collection(
            TradeOpportunity::when(
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
    public function show(TradeOpportunity $tradeOpportunity): JsonResource
    {
        return new TradeOpportunityResource($tradeOpportunity);
    }

    /**
     * Refetch all trade opportunities.
     */
    public function refetch(PaginationRequest $request)
    {
        UpdateOrRemoveTradeOpportunitiesAction::dispatchSync();

        return $this->index($request);
    }
}
