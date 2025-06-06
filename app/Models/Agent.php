<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $account_id
 * @property string $symbol
 * @property string $headquarters
 * @property int $credits
 * @property string $starting_faction
 * @property int $ship_count
 * @property int $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contract> $contracts
 * @property-read int|null $contracts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ship> $ships
 * @property-read int|null $ships_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Survey> $surveys
 * @property-read System $starting_system
 * @property-read int|null $surveys_count
 * @property-read User $user
 * @property-read Waypoint|null $headquarter
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Agent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent query()
 * @method static \Illuminate\Database\Eloquent\Builder|Agent searchBySymbol(string $search = '')
 *
 * @mixin \Eloquent
 */
class Agent extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'account_id',
        'symbol',
        'headquarters',
        'credits',
        'starting_faction',
        'ship_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function getStartingSystemAttribute(): System
    {
        return $this->headquarter->system;
    }

    public function headquarter(): HasOne
    {
        return $this->hasOne(Waypoint::class, 'symbol', 'headquarters')
            ->where('symbol', $this->headquarters);
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
            'account_id' => 'string',
            'symbol' => 'string',
            'headquarters' => 'string',
            'credits' => 'integer',
            'starting_faction' => 'string',
            'ship_count' => 'integer',
        ];
    }
}
