<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ShipMount extends Pivot
{
    protected $fillable = [
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];
}
