<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FactionTraits;

class FactionTrait extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'symbol' => FactionTraits::class,
        'name' => 'string',
        'description' => 'string',
    ];

    public function faction()
    {
        return $this->belongsToMany(Faction::class);
    }
}
