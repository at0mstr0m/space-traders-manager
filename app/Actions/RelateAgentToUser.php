<?php

namespace App\Actions;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Lorisleiva\Actions\Concerns\AsAction;

class RelateAgentToUser
{
    use AsAction;

    public function handle(User $user, Arrayable $agentData)
    {
        if (is_array($agentData)) {
            $agentData = $agentData->toArray();
        }

        $attributes = [
            'account_id' => $agentData['accountId'],
            'symbol' => $agentData['symbol'],
            'headquarters' => $agentData['headquarters'],
            'starting_faction' => $agentData['startingFaction'],
        ];

        $agent = Agent::firstWhere($attributes)
            ?? Agent::new();

        $agent->setAttributes([
            ...$attributes,
            'user_id', $user->id,
            'credits' => $agentData['credits']
        ])->save();

        return $agent;
    }
}
