<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cargo extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
        'units',
    ];

    protected $casts = [
        'symbol' => 'string',
        'name' => 'string',
        'description' => 'string',
        'units' => 'integer',
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }
}
