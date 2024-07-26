<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ShipConditionEventComponents;
use App\Enums\ShipConditionEvents;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
