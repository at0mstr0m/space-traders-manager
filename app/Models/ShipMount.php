<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\ShipMount.
 *
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

    protected $casts = [
        'quantity' => 'integer',
    ];
}
