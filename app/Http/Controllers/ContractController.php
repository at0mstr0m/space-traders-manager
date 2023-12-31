<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContractResource;
use App\Jobs\UpdateContracts;
use App\Models\Contract;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ContractResource::collection(Contract::paginate());
    }

    /**
     * Refetch contracts.
     */
    public function refetch()
    {
        UpdateContracts::dispatchSync(request()->user()->agent);

        return $this->index();
    }

    /**
     * Accept contract.
     */
    public function accept(Contract $contract)
    {
        $contract->accept();

        return new ContractResource($contract);
    }
}
