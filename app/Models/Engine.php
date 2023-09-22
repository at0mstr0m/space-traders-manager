<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Engine extends Model
{
    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }
}
