<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Casts\CarbonCast;
use App\Interfaces\UpdatesAgent;
use App\Interfaces\UpdatesShip;
use App\Models\Agent;
use App\Models\Ship;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class JumpShipData extends Data implements UpdatesShip, UpdatesAgent
{
    public function __construct(
        #[MapInputName('cooldown.expiration')]
        #[WithCast(CarbonCast::class)]
        public Carbon $cooldown,
        #[MapInputName('nav')]
        public NavigationData $nav,
        #[MapInputName('transaction')]
        public MarketTransactionData $transaction,
        #[MapInputName('agent')]
        public AgentData $agent,
    ) {}

    public function updateAgent(Agent $agent): Agent
    {
        return $this->agent->updateAgent($agent);
    }

    public function updateShip(Ship $ship): Ship
    {
        $this->updateAgent($ship->agent)->save();

        return $this->nav->updateShip($ship);
    }
}
