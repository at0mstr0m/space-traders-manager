<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\UpdateContractAction;
use App\Enums\ContractTypes;
use App\Enums\FactionSymbols;
use App\Helpers\SpaceTraders;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        'identification',
        'faction_symbol',
        'type',
        'accepted',
        'fulfilled',
        'deadline',
        'payment_on_accepted',
        'payment_on_fulfilled',
    ];

    protected $casts = [
        'identification' => 'string',
        'faction_symbol' => FactionSymbols::class,
        'type' => ContractTypes::class,
        'accepted' => 'boolean',
        'fulfilled' => 'boolean',
        'deadline' => 'datetime',
        'payment_on_accepted' => 'integer',
        'payment_on_fulfilled' => 'integer',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function refetch(): static
    {
        /** @var SpaceTraders */
        $api = app(SpaceTraders::class);

        return UpdateContractAction::run(
            $api->getContract($this->identification),
            $this->agent
        );
    }

    public function accept(): static
    {
        /** @var SpaceTraders */
        $api = app(SpaceTraders::class);

        return UpdateContractAction::run(
            $api->acceptContract($this->identification)->contract,
            $this->agent
        );
    }

    public function fulfill(): static
    {
        /** @var SpaceTraders */
        $api = app(SpaceTraders::class);

        return $api->fulfillContract($this->identification)
            ->updateContract($this);
    }
}
