<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Model;
use App\Traits\DataHasModel;
use Spatie\LaravelData\Data;
use App\Interfaces\WithModelInstance;
use App\Interfaces\GeneratableFromResponse;
use App\Traits\HasCollectionFromResponse;

class AgentData extends Data implements WithModelInstance, GeneratableFromResponse
{
    use DataHasModel;
    use HasCollectionFromResponse;

    public function __construct(
        public string $symbol,
        public string $headquarters,
        public int $credits,
        public string $startingFaction,
        public ?int $shipCount = null,
        public ?string $accountId = null,
    ) {
    }

    public static function fromResponse(array $response): static
    {
        return new static(
            symbol: $response['symbol'],
            headquarters: $response['headquarters'],
            credits: $response['credits'],
            startingFaction: $response['startingFaction'],
            shipCount: $response['shipCount'],
            accountId: data_get($response, 'accountId'),
        );
    }

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
}
