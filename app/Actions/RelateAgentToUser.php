<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\AgentData;
use App\Models\Agent;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class RelateAgentToUser
{
    use AsAction;

    public function handle(User $user, AgentData $agentData)
    {
        $agent = Agent::firstWhere('account_id', $agentData->accountId)
            ?? $agentData->makeModelInstance();

        $agent->user()->associate($user)->save();

        return $agent;
    }
}
