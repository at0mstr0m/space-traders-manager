<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Model;
use App\Traits\HasModel;
use Spatie\LaravelData\Data;
use App\Interfaces\WithModelInstance;
use App\Interfaces\GeneratableFromResponse;

class AgentData extends Data implements WithModelInstance, GeneratableFromResponse
{
    use HasModel;

    public function __construct(
        public string $accountId,
        public string $symbol,
        public string $headquarters,
        public int $credits,
        public string $startingFaction,
        public ?int $shipCount = null,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            accountId: $response['accountId'],
            symbol: $response['symbol'],
            headquarters: $response['headquarters'],
            credits: $response['credits'],
            startingFaction: $response['startingFaction'],
            shipCount: $response['shipCount'],
        );
    }

    public function makeModelInstance(): Model
    {
        return $this->makeModel()->setAttributes([
            'account_id' => $this->accountId,
            'symbol' => $this->symbol,
            'headquarters' => $this->headquarters,
            'credits' => $this->credits,
            'starting_faction' => $this->startingFaction,
            'ship_count' => $this->shipCount,
        ]);
    }
}
