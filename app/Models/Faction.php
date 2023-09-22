<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faction extends Model
{
    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class);
    }
}
