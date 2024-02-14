<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * App\Models\ShipModule.
 *
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

    protected $casts = [
        'quantity' => 'integer',
    ];
}
