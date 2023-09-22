<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deposit extends Model
{
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class);
    }
}
