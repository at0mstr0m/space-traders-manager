<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\FactionSymbols;
use App\Interfaces\UpdatesAgent;
use App\Interfaces\WithModelInstance;
use App\Models\Agent;
use App\Models\Model;
use App\Traits\DataHasModel;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class AgentData extends Data implements WithModelInstance, UpdatesAgent
{
    use DataHasModel;

    public function __construct(
        #[MapInputName('symbol')]
        public string $symbol,
        #[MapInputName('headquarters')]
        public string $headquarters,
        #[MapInputName('credits')]
        public int $credits,
        #[MapInputName('startingFaction')]
        #[WithCast(EnumCast::class)]
        public FactionSymbols $startingFaction,
        #[MapInputName('shipCount')]
        public ?int $shipCount = null,
        #[MapInputName('accountId')]
        public ?string $accountId = null,
    ) {}

    public function makeModelInstance(): Model
    {
        if (!$this->accountId) {
            throw new \Exception('Cannot create an Agent Model without an accountId');
        }

        return $this->makeModel([
            'account_id' => $this->accountId,
            'symbol' => $this->symbol,
            'headquarters' => $this->headquarters,
            'credits' => $this->credits,
            'starting_faction' => $this->startingFaction,
            'ship_count' => $this->shipCount,
        ]);
    }

    public function updateAgent(Agent $agent): Agent
    {
        $agent->fill([
            'credits' => $this->credits,
            'ship_count' => $this->shipCount,
        ]);

        return $agent;
    }
}
