<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SurveySizes;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Survey
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $signature
 * @property string $waypoint_symbol
 * @property \Illuminate\Support\Carbon $expiration
 * @property SurveySizes $size
 * @property int $agent_id
 * @property string $raw_response
 * @property-read \App\Models\Deposit $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deposit> $deposits
 * @property-read int|null $deposits_count
 * @method static \Illuminate\Database\Eloquent\Builder|Survey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Survey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Survey query()
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereExpiration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereRawResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Survey whereWaypointSymbol($value)
 * @mixin \Eloquent
 */
class Survey extends Model
{
    use Prunable;

    protected $fillable = [
        'signature',
        'waypoint_symbol',
        'expiration',
        'size',
        'raw_response',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'signature' => 'string',
        'waypoint_symbol' => 'string',
        'expiration' => 'datetime',
        'size' => SurveySizes::class,
        'raw_response' => 'string',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(Deposit::class);
    }

    public function toRequestableObject(): array
    {
        return [
            'signature' => $this->signature,
            'symbol' => $this->waypoint_symbol,
            'deposits' => $this->deposits
                ->map(
                    fn (Deposit $deposit) => ['symbol' => $deposit->symbol->value]
                )->all(),
            'expiration' => $this->expiration->toIsoString(),
            'size' => $this->size->value,
        ];
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('expiration', '<=', now()->subMinutes(2));
    }
}
