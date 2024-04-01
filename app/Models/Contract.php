<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\UpdateContractAction;
use App\Enums\ContractTypes;
use App\Enums\FactionSymbols;
use App\Helpers\SpaceTraders;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Contract.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $agent_id
 * @property string $identification
 * @property FactionSymbols $faction_symbol
 * @property ContractTypes $type
 * @property bool $accepted
 * @property bool $fulfilled
 * @property Carbon $deadline
 * @property string $deadline_to_accept
 * @property int $payment_on_accepted
 * @property int $payment_on_fulfilled
 * @property-read Agent $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery> $deliveries
 * @property-read int|null $deliveries_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereDeadlineToAccept($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereFactionSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereFulfilled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereIdentification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract wherePaymentOnAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract wherePaymentOnFulfilled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contract whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Contract extends Model
{
    protected $fillable = [
        'identification',
        'faction_symbol',
        'type',
        'accepted',
        'fulfilled',
        'deadline',
        'deadline_to_accept',
        'payment_on_accepted',
        'payment_on_fulfilled',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'identification' => 'string',
            'faction_symbol' => FactionSymbols::class,
            'type' => ContractTypes::class,
            'accepted' => 'boolean',
            'fulfilled' => 'boolean',
            'deadline' => 'datetime',
            'deadline_to_accept' => 'datetime',
            'payment_on_accepted' => 'integer',
            'payment_on_fulfilled' => 'integer',
        ];
    }
}
