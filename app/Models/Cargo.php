<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property TradeSymbols $symbol
 * @property string $name
 * @property string $description
 * @property int $ship_id
 * @property int $units
 * @property-read Ship $ship
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Cargo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cargo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cargo query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cargo searchBySymbol(string $search = '')
 *
 * @mixin \Eloquent
 */
class Cargo extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'units',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'symbol' => TradeSymbols::class,
            'name' => 'string',
            'description' => 'string',
            'units' => 'integer',
        ];
    }
}
