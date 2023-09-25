<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AgentData;
use App\Helpers\SpaceTraders;
use App\Models\Agent;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class RelateAgentToUser
{
    use AsAction;

    private SpaceTraders $api;

    public function __construct()
    {
        $this->api = app(SpaceTraders::class);
    }

    public function handle(?User $user = null, ?AgentData $agentData = null)
    {
        $user ??= User::first();
        $agentData ??= $this->api->getAgent();
        $agent = Agent::firstWhere('account_id', $agentData->accountId)
            ?? $agentData->makeModelInstance();

        $agent->user()->associate($user)->save();

        return $agent;
    }
}
