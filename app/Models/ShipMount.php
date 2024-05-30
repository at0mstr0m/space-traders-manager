<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|ShipMount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipMount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipMount query()
 *
 * @mixin \Eloquent
 */
class ShipMount extends Pivot
{
    protected $fillable = [
        'quantity',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }
}
