<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        return TransactionResource::collection(
            Transaction::where('agent_symbol', $request->user()->agent->symbol)
                ->orderBy(
                    $request->sortBy('timestamp'),
                    $request->sortDirection('desc')
                )->paginate(
                    $request->perPage(),
                    page: $request->page()
                )
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): TransactionResource
    {
        return new TransactionResource($transaction);
    }
}
