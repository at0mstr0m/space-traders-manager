<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|ShipModule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipModule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShipModule query()
 *
 * @mixin \Eloquent
 */
class ShipModule extends Pivot
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
