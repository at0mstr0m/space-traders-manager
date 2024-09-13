<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ShipConditionEventComponents;
use App\Enums\ShipConditionEvents;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $ship_id
 * @property ShipConditionEvents $symbol
 * @property ShipConditionEventComponents $component
 * @property string $name
 * @property string $description
 * @property-read Ship $ship
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ShipConditionEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipConditionEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipConditionEvent query()
 *
 * @mixin \Eloquent
 */
class ShipConditionEvent extends Model
{
    protected $fillable = [
        'symbol',
        'component',
        'name',
        'description',
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'symbol' => ShipConditionEvents::class,
            'component' => ShipConditionEventComponents::class,
            'name' => 'string',
            'description' => 'string',
        ];
    }
}
