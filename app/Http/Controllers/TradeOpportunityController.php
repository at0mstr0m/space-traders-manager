<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateOrRemoveTradeOpportunitiesAction;
use App\Http\Resources\TradeOpportunityResource;
use App\Models\TradeOpportunity;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeOpportunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResource
    {
        return TradeOpportunityResource::collection(TradeOpportunity::paginate());
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
    public function refetch()
    {
        UpdateOrRemoveTradeOpportunitiesAction::dispatchSync();

        return $this->index();
    }
}
