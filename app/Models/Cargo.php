<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TradeSymbols;
use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cargo extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'symbol',
        'name',
        'description',
        'units',
    ];

    protected $casts = [
        'symbol' => TradeSymbols::class,
        'name' => 'string',
        'description' => 'string',
        'units' => 'integer',
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}
