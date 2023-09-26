<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FindableBySymbol;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faction extends Model
{
    use FindableBySymbol;

    protected $fillable = [
        'symbol',
        'name',
        'headquarters',
        'description',
        'is_recruiting',
    ];

    protected $casts = [
        'is_recruiting' => 'boolean',
    ];

    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class);
    }

    public function traits(): BelongsToMany
    {
        return $this->belongsToMany(FactionTrait::class);
    }
}
