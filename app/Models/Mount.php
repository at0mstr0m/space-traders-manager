<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mount extends Model
{
    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(Ship::class);
    }

    public function deposits(): BelongsToMany
    {
        return $this->belongsToMany(Deposit::class);
    }
}
